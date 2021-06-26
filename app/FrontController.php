<?php

namespace Chell;

use Throwable;

use Chell\Controllers\ErrorController;
use Chell\Exceptions\ChellException;
use Chell\Messages\TranslatorWrapper;
use Chell\Models\SettingsContainer;
use Chell\Plugins\SecurityPlugin;

use Phalcon\Crypt;
use Phalcon\Loader;
use Phalcon\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Http\Request;
use Phalcon\Debug\Dump;

/**
 * Frontcontroller sets up Phalcon to run the application.
 */
class FrontController
{
    private ConfigIni $config;
    private SettingsContainer $settings;
    private FactoryDefault $di;
    private Application $application;
    private bool $dbSet = false;

    private array $jsFiles = [
        'dist/js/jquery.js',
        'dist/js/jquery.fancybox.js',
        'dist/js/bootstrap.js',
        'dist/js/bootstrap-select.js',
        'dist/js/bootstrap-tabcollapse.js',
        'dist/js/bootstrap-toggle.js',
        'dist/js/jquery.bootstrap-touchspin.js',
        'dist/js/jquery.vibrate.js',
        'dist/js/jquery.tinytimer.js',
        'dist/js/jquery.isloading.js',
        'dist/js/jquery.fullscreen.js',
        'dist/js/chartist.js',
        'dist/js/chartist-plugin-legend.js',
        'dist/js/waves.js',
        'dist/js/spark-md5.js',
        'dist/js/toggle-passwords.js',
        'dist/js/general.js',
    ];

    private array $cssFiles = [
        'dist/css/jquery.fancybox.css',
        'dist/css/waves.css',
        'dist/css/bootstrap-select.css',
        'dist/css/bootstrap-toggle.css',
        'dist/css/jquery.bootstrap-touchspin.css',
        'dist/css/default.css'
    ];

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

        define('APP_PATH', realpath('./') . '/');
        define('BASEPATH', (new Url())->getBaseUri());

        $this->di = new FactoryDefault();
        $this->di->set('config', $this->config = new ConfigIni(APP_PATH . 'app/config/config.ini'));
        define('DEBUG', $this->config->general->debug);

        $this->registerNamespaces();
        $this->setExceptionHandler();
        $this->setDB();
        $this->setSettings();
        $this->setDispatcher();
        $this->setCrypt();
        $this->setDisplayErrors();
        $this->setViewProvider();
        $this->setSession();
        $this->application = new Application($this->di);
        $this->application->view->executionTime = $executionTime;
        $this->setAssets();
        $this->setTitle();
        $this->setTranslator();
    }

    /**
     * Initializes PHP exception handler to Chell's custom handler.
     */
    private function setExceptionHandler()
    {
        set_exception_handler([&$this, 'ExceptionHandler']);
    }

    /**
     * function defined for PHP's set_exception_handler.
     *
     * @param Throwable $exception  The exception being thrown.
     */
    public function ExceptionHandler(Throwable $exception)
    {
        if (strpos(basename($_SERVER['REQUEST_URI']), '.') !== false)
        {
            die(header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found'));
        }

        require_once(APP_PATH . 'app/controllers/ErrorController.php');

        new ErrorController(new ChellException($exception));
    }

    /**
     * Sets Phalcon's dispatcher and a beforeExecuteRoute to setup the SecurityPlugin, which enforces logins.
     */
    private function setDispatcher()
    {
        $this->di->set('dispatcher', function () {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin());

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
        $loader = new Loader();
        $loader->registerNamespaces([
            'Chell\Controllers'                 => APP_PATH . 'app/controllers/',
            'Chell\Exceptions'                  => APP_PATH . 'app/exceptions/',
            'Chell\Forms'                       => APP_PATH . 'app/forms/',
            'Chell\Forms\FormFields'            => APP_PATH . 'app/forms/formfields/',
            'Chell\Forms\FormFields\Dashboard'  => APP_PATH . 'app/forms/formfields/dashboard/',
            'Chell\Forms\FormFields\General'    => APP_PATH . 'app/forms/formfields/general/',
            'Chell\Forms\Validators'            => APP_PATH . 'app/forms/validators/',
            'Chell\Messages'                    => APP_PATH . 'app/messages/',
            'Chell\Models'                      => APP_PATH . 'app/models/',
            'Chell\Models\Kodi'                 => APP_PATH . 'app/models/kodi/',
            'Chell\Plugins'                     => APP_PATH . 'app/plugins/',
            'Duo'                               => APP_PATH . 'app/vendor/duosecurity/duo_php/src/',
            'Davidearl\WebAuthn'                => APP_PATH . 'app/vendor/davidearl/webauthn/WebAuthn',
            'CBOR'                              => APP_PATH . 'app/vendor/2tvenom/cborencode/src',
            'phpseclib'                         => APP_PATH . 'app/vendor/phpseclib/phpseclib/phpseclib/',
            'WriteiniFile'                      => APP_PATH . 'app/vendor/magicalex/write-ini-file/src/'
        ])->register();
    }

    /**
     * Setup the database services.
     *
     * @param ConfigIni $config	The config object representing config.ini.
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
                'charset'  => 'utf8'
            ]);
        });

        $this->dbSet = true;
    }

    /**
     * Setup Phalcon view provider.
     */
    private function setViewProvider()
    {
        $this->di->set('view', function () {
            $view = new View();
            $view->setViewsDir(APP_PATH . 'app/views/');
            return $view;
        });
    }

    /**
     * Instantiate session.
     *
     * @param SettingsContainer $settings    The settings object representing all settings in the database.
     */
    private function setSession()
    {
        $settings = $this->settings;
        $dbSet = $this->dbSet;

        $this->di->setShared('session', function () use ($settings, $dbSet) {
            $session = new Manager();
            $adapter = null;

            if ($dbSet && $settings->redis->enabled)
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
                $adapter = new Stream(['savePath' => $savePath]);
            }

            $session->setAdapter($adapter);
            $session->setName(ini_get('session.name') . '_' . str_replace(' ', '_', $settings->application->title));
            $session->start();

            return $session;
        });
    }

    /**
     * Sets the static assets files, such as JS and CSS.
     */
    private function setAssets()
    {
        $version = $this->settings->application->version;

        if (DEBUG)
        {
            foreach($this->cssFiles as $file)
            {
                $this->application->assets->collection('header')->addCss($file, true, false, [], $version, true);
            }

            foreach($this->jsFiles as $file)
            {
                $this->application->assets->collection('general')->addJs($file, true, false, ['defer' => 'defer'], $version, true);
            }
        }
        else
        {
            $this->application->assets->collection('header')->addCss('dist/css/bundle.min.css', true, false, [], $version, true);
            $this->application->assets->collection('general')->addJs('dist/js/general.min.js', true, false, ['defer' => 'defer'], $version, true);
        }
    }

    /**
     * Sets the title for the application.
     */
    private function setTitle()
    {
        $this->application->tag->setTitle($this->settings->application->title);
    }

    /**
     * Sets the translator for use in views.
     */
    private function setTranslator()
    {
        $language = $this->application->request->getBestLanguage();
        $this->application->view->trans = new TranslatorWrapper(APP_PATH . 'app/messages/' . $language . '.php');
        $this->di->set('translator', $this->application->view->trans);
    }

    /**
     * Retrieves al settings from the database and structures then in a hierarchical structur.
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
        $uri = str_replace(BASEPATH, '', '/' . (new Request())->getURI());
        return $this->application->handle($uri)->getContent();
    }
}