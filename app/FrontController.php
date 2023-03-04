<?php

namespace Chell;

use Throwable;

use Chell\Controllers\ErrorController;
use Chell\Exceptions\ChellException;
use Chell\Messages\TranslatorWrapper;
use Chell\Models\SettingsContainer;
use Chell\Plugins\SecurityPlugin;
use Chell\Plugins\ChellLogger;

use Phalcon\Encryption\Crypt;
use Phalcon\Autoload\Loader;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\Stream as LogStream;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Session\Adapter\Stream as SessionStream;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Http\Request;
use Phalcon\Support\Debug\Dump;
use Phalcon\Tag;

/**
 * Frontcontroller sets up Phalcon to run the application.
 */
class FrontController
{
    private ConfigIni $config;
    private SettingsContainer $settings;
    private FactoryDefault $di;
    private Application $application;
    private ChellLogger $logger;
    private bool $dbSet = false;

    /**
     * Initialize Phalcon.
     */
    public function __construct()
    {
        $executionTime = -microtime(true);

        function dump($dump)
        {
            if (DEBUG)
            {
                die((new Dump())->variable($dump));
            }
        }

        chdir('../');
        define('APP_PATH', realpath('./') . '/');
        define('PUBLIC_PATH', APP_PATH . 'public/');
        define('BASEPATH', (new Url())->getBaseUri());

        $this->di = new FactoryDefault();
        $this->di->set('config', $this->config = new ConfigIni(APP_PATH . 'app/config/config.ini'));
        define('DEBUG', $this->config->general->debug);

        $this->registerNamespaces();
        $this->setLogger();
        $this->setDispatcher();
        $this->setDB();
        $this->setSettings();
        $this->setCrypt();
        $this->setDisplayErrors();
        $this->setViewProvider();
        $this->setSession();
        $this->application = new Application($this->di);
        $this->application->view->executionTime = $executionTime;
        $this->setTitle();
        $this->setTranslator();

        if ($timezone = getenv('TZ'))
        {
            date_default_timezone_set($timezone);
        }
    }

    /**
     * Sets Phalcon's dispatcher and a beforeExecuteRoute to setup the SecurityPlugin, which enforces logins.
     */
    private function setDispatcher()
    {
        $logger = $this->logger;
        $this->di->set('dispatcher', function () use ($logger) {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin());
            $eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, Throwable $exception) use ($logger) {
                $logger->critical($exception->getMessage());
                $logger->debug('File: ' . $exception->getFile() . PHP_EOL . 'Line: ' . $exception->getLine() . PHP_EOL . 'Stacktrace:' . $exception->getTraceAsString());

                require_once(APP_PATH . 'app/controllers/ErrorController.php');
                (new ErrorController())->initialize(new ChellException($exception));
            });

            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('Chell\Controllers');

            return $dispatcher;
        });
    }

    /**
     * Sets up Phalcon's crypt, whith the in settings defined crypt key. Used for encrypting/decrypting password for example.
     */
    private function setCrypt()
    {
        $settings = $this->settings;
        $this->di->set('crypt', function() use ($settings) {
            $crypt = new Crypt();
            $crypt->setKey($settings->application->phalcon_crypt_key);
            return $crypt;
        });
    }

    /**
     * Show errors in browser, decided by flag in config.
     */
    private function setDisplayErrors()
    {
        ini_set('display_errors', DEBUG ? 'on' : 'off');
    }

    /**
     * Register all namespaces and directories used by Phalcon.
     */
    private function registerNamespaces()
    {
        require APP_PATH . 'vendor/autoload.php';

        $loader = new Loader();
        $loader->setNamespaces([
            'Chell\Controllers'                 => [APP_PATH . 'app/controllers/'],
            'Chell\Exceptions'                  => [APP_PATH . 'app/exceptions/'],
            'Chell\Forms'                       => [APP_PATH . 'app/forms/'],
            'Chell\Forms\FormFields'            => [APP_PATH . 'app/forms/formfields/'],
            'Chell\Forms\FormFields\Dashboard'  => [APP_PATH . 'app/forms/formfields/dashboard/'],
            'Chell\Forms\FormFields\General'    => [APP_PATH . 'app/forms/formfields/general/'],
            'Chell\Forms\Validators'            => [APP_PATH . 'app/forms/validators/'],
            'Chell\Messages'                    => [APP_PATH . 'app/messages/'],
            'Chell\Models'                      => [APP_PATH . 'app/models/'],
            'Chell\Models\Torrents'             => [APP_PATH . 'app/models/Torrents'],
            'Chell\Plugins'                     => [APP_PATH . 'app/plugins/'],
        ])->register();
    }

    /**
     * Setup the database services.
     */
    private function setDB()
    {
        if (empty($this->config->database->host) || empty($this->config->database->username) || empty($this->config->database->password) || empty($this->config->database->name))
        {
            $this->dbSet = false;
            return;
        }

        $config = $this->config;
        $this->di->set('db', function() use ($config) {
            return new DbAdapter([
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->name,
                'charset'  => 'utf8',
                "options"    => [\PDO::ATTR_PERSISTENT => 1],
            ]);
        });

        $this->dbSet = true;
    }

    /**
     * Setup Phalcon view provider.
     */
    private function setViewProvider()
    {
        $eventsManager = new EventsManager();
        $this->di->set('vieweventmanager', $eventsManager);

        $this->di->set('view', function () use($eventsManager) {
            $view = new View();
            $view->setEventsManager($eventsManager);
            $view->setViewsDir(APP_PATH . 'app/views/');
            return $view;
        });
    }

    /**
     * Instantiate session.
     */
    private function setSession()
    {
        $settings = $this->settings;
        $dbSet = $this->dbSet;

        $this->di->setShared('session', function () use ($settings, $dbSet) {
            $session = new Manager();
            $adapter = null;

            if ($dbSet && $settings->redis?->enabled)
            {
                $adapter = new Redis(new AdapterFactory(new SerializerFactory()), [
                   'host'   => $settings->redis->host,
                   'port'   => $settings->redis->port,
                   'index'  => '1',
                   'auth'   => $settings->redis->auth
               ]);
            }
            else
            {
                $savePath = ini_get('session.save_path');
                $adapter = new SessionStream(['savePath' => $savePath ? $savePath : '/tmp']);
            }

            $session->setAdapter($adapter);
            $session->setName(ini_get('session.name') . '_' . str_replace(' ', '_', $settings->application->title));
            $session->start();

            return $session;
        });
    }

    /**
     * Sets the title for the application.
     */
    private function setTitle()
    {
        Tag::setTitle($this->settings->application->title);
    }

    /**
     * Sets the translator for use in views.
     */
    private function setLogger()
    {
        $adapter = new LogStream(APP_PATH . '/app/logs/' . date('Y-m-d') . '.log');
        $logger  = new ChellLogger('messages', [ 'main' => $adapter ]);
        $logger->setLogLevel(DEBUG ? ChellLogger::DEBUG : ChellLogger::ERROR);
        $this->logger = $logger;

        $this->di->set('logger', function() use ($logger) {
            return $logger;
        });
    }

    private function setTranslator()
    {
        $language = $this->application->request->getBestLanguage();
        $language= current(explode('-', $language));

        $this->application->view->trans = new TranslatorWrapper(APP_PATH . 'app/messages/' . $language);
        $this->di->set('translator', $this->application->view->trans);
    }

    /**
     * Retrieves all settings from the database and structures them in a hierarchical structure.
     */
    private function setSettings()
    {
        $structuredSettings = new SettingsContainer($this->config);
        $this->di->set('settings', $structuredSettings);
        $this->settings =  $structuredSettings;
    }

    /**
     * Echoes the HTML to the browser.
     *
     * @return string   The complete HTML of the request.
     */
    public function __toString() : string
    {
        $uri = (new Request())->getURI();
        return $this->application->handle($uri)->getContent();
    }
}