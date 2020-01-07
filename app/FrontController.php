<?php

namespace Chell;

use Chell\Controllers\ErrorController;
use Chell\Exceptions\ChellException;
use Chell\Plugins\SecurityPlugin;
use Chell\Messages\TranslatorWrapper;

use Phalcon\Crypt;
use Phalcon\Loader;
use Phalcon\Url as UrlProvider;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Http\Request;

/**
 * Frontcontroller sets up Phalcon to run the application.
 */
class FrontController
{
    private $title;
    private $config;
    private $di;
    private $application;

    /**
     * Initialize Phalcon.
     */
    public function __construct()
    {
        $executionTime = -microtime(true);
        define('APP_PATH', realpath('..') . '/');

        set_exception_handler(array(&$this, 'ExceptionHandler'));

        $this->config = $config = new ConfigIni(APP_PATH . 'app/config/config.ini');
        $this->di = new FactoryDefault();
        $this->di->set('config', $config);


        $this->registerNamespaces();

        $this->di->set('dispatcher', function () {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);

            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('Chell\Controllers');

            return $dispatcher;
        });

        $this->di->set('crypt', function() use ($config) {
            $crypt = new Crypt();
            $crypt->setKey($config->application->phalconCryptKey);
            return $crypt;
        });

        $this->setDisplayErrors();
        $this->title = $config->application->title;

        $this->setDB($config);
        $this->setViewProvider($config);
        $this->setURLProvider($config);
        $this->setSession();

        $this->application = new Application($this->di);
        $this->application->view->executionTime = $executionTime;

        $this->setAssets();
        $this->setTitle();
        $this->setTranslator();
    }

    /**
     * function defined for PHP's set_exception_handler.
     *
     * @param \Throwable $exception  The exception being thrown.
     */
    public function ExceptionHandler(\Throwable $exception)
    {
        require_once(APP_PATH . 'app/controllers/ErrorController.php');

        new ErrorController(new ChellException($exception), $this->config);
    }

    /**
     * Show errors in browser, decided by flag in config.
     */
    private function setDisplayErrors()
    {
        ini_set('display_errors', $this->config->application->debug ? 'on' : 'off');
    }

    /**
     * Register all namespaces and directories used by Phalcon.
     */
    private function registerNamespaces()
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            'Chell\Controllers' => APP_PATH . $this->config->application->controllersDir,
            'Chell\Exceptions'  => APP_PATH . $this->config->application->exceptionsDir,
            'Chell\Forms'       => APP_PATH . $this->config->application->formsDir,
            'Chell\Messages'    => APP_PATH . $this->config->application->messagesDir,
            'Chell\Models'      => APP_PATH . $this->config->application->modelsDir,
            'Chell\Models\Kodi' => APP_PATH . $this->config->application->modelsDir . 'kodi/',
            'Chell\Plugins'     => APP_PATH . $this->config->application->pluginsDir,
            'Duo'               => APP_PATH . $this->config->application->duoDir,
        ])->register();
    }

    /**
     * Setup the database services.
     */
    private function setDB($config)
    {
        $this->di->set('db', function() use ($config) {
            return new DbAdapter(array(
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->name,
                'charset'  => 'utf8'
            ));
        });

        $this->di->set('dbKodiMusic', function() use ($config) {
            return new DbAdapter(array(
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->kodiMusic,
                'charset'  => 'utf8'
            ));
        });

        $this->di->set('dbKodiVideo', function() use ($config) {
            return new DbAdapter(array(
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->kodiVideo,
                'charset'  => 'utf8'
            ));
        });
    }

    /**
     * Setup Phalcon view provider.
     */
    private function setViewProvider($config)
    {
        $this->di->set('view', function () use ($config) {
            $view = new View();
            $view->setViewsDir(APP_PATH . $config->application->viewsDir);
            return $view;
        });
    }

    /**
     * Setup Phalcon URL provider.
     */
    private function setURLProvider($config)
    {
        $this->di->set('url', function () use ($config) {
            $url = new UrlProvider();
            $url->setBaseUri($config->application->baseUri);
            return $url;
        });
    }

    /**
     * Instantiate session.
     */
    private function setSession()
    {
        $this->di->setShared('session', function () {
            $session = new Manager();
            $redis   = new Redis(new AdapterFactory(new SerializerFactory()), [
                'host'  => 'localhost',
                'port'  => 6379,
                'index' => '1',
            ]);

            $session->setAdapter($redis);
            $session->start();

            return $session;
        });
    }

    private function setAssets()
    {
        if($this->config->application->debug)
        {
            $this->application->assets->collection('header')->addCss('css/default/default.css');

            $this->application->assets->collection('default')->addJs('js/vendor/jquery-3.4.1.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/jquery.fancybox.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/bootstrap.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/bootstrap-select/bootstrap-select.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/bootstrap-tabcollapse.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/bootstrap-toggle.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/jquery.bootstrap-touchspin.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/jquery.vibrate.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/jquery.tinytimer.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/jquery.isloading.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/jquery.fullscreen.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/chartist/chartist.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/chartist/chartist-plugin-legend.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/waves.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/vendor/md5.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('default')->addJs('js/default.js', true, false, array('defer' => 'defer'));

            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/couchpotato.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/devices.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/gallery.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/hyperv-admin.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/motion.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/nowplaying.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/opcache.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/phpsysinfo.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/pihole.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/sickrage.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/speedtest.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/transmission.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard-blocks/youless.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard.js', true, false, array('defer' => 'defer'));

            $this->application->assets->collection('settings')->addJs('js/settings.js', true, false, array('defer' => 'defer'));
        }
        else
        {
            $this->application->assets->collection('header')->addCss('css/default/default.min.css');

            $this->application->assets->collection('default')->addJs('js/default.min.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('dashboard')->addJs('js/dashboard.min.js', true, false, array('defer' => 'defer'));
            $this->application->assets->collection('settings')->addJs('js/settings.min.js', true, false, array('defer' => 'defer'));
        }
    }

    /**
     * Sets the title for the application.
     */
    private function setTitle()
    {
        $this->application->tag->setTitle($this->application->view->title = $this->title);
    }

    /**
     * Sets the translator for use in views.
     */
    private function setTranslator()
    {
        $language = $this->application->request->getBestLanguage();
        $this->application->view->trans = new TranslatorWrapper(APP_PATH . 'app/messages/' . $language . '.php');
    }

    /**
     * Echoes the HTML to the browser.
     * @return mixed    The complete HTML of the request.
     */
    public function ToString()
    {
        $request = new Request();
        return $this->application->handle(str_replace($this->config->application->baseUri, '', '/' . $request->getURI()))->getContent();
    }
}