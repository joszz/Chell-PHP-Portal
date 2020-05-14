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
use Phalcon\Session\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Http\Request;

/**
 * Frontcontroller sets up Phalcon to run the application.
 */
class FrontController
{
    private $config;
    private $di;
    private $application;

    private $jsFiles = [
        'default' => [
            'vendor/jquery-3.5.1.js',
            'vendor/jquery.fancybox.js',
            'vendor/bootstrap.js',
            'vendor/bootstrap-select/bootstrap-select.js',
            'vendor/bootstrap-tabcollapse.js',
            'vendor/bootstrap-toggle.js',
            'vendor/jquery.bootstrap-touchspin.js',
            'vendor/jquery.vibrate.js',
            'vendor/jquery.tinytimer.js',
            'vendor/jquery.isloading.js',
            'vendor/jquery.fullscreen.js',
            'vendor/chartist/chartist.js',
            'vendor/chartist/chartist-plugin-legend.js',
            'vendor/waves.js',
            'vendor/md5.js',
            'toggle-passwords.js',
            'default.js',
        ],
        'dashboard' => [
            'dashboard-blocks/couchpotato.js',
            'dashboard-blocks/devices.js',
            'dashboard-blocks/gallery.js',
            'dashboard-blocks/hyperv-admin.js',
            'dashboard-blocks/motion.js',
            'dashboard-blocks/nowplaying.js',
            'dashboard-blocks/opcache.js',
            'dashboard-blocks/phpsysinfo.js',
            'dashboard-blocks/pihole.js',
            'dashboard-blocks/sickrage.js',
            'dashboard-blocks/speedtest.js',
            'dashboard-blocks/transmission.js',
            'dashboard-blocks/youless.js',
            'dashboard.js',
        ],
        'settings' => ['settings.js']
    ];

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

        $this->registerNamespaces();
        $this->setDisplayErrors();
        $this->setDB($config);
        $this->setViewProvider($config);
        $this->setURLProvider($config);
        $this->setSession($config);

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
            'Chell\Controllers'             => APP_PATH . $this->config->application->controllersDir,
            'Chell\Exceptions'              => APP_PATH . $this->config->application->exceptionsDir,
            'Chell\Forms'                   => APP_PATH . $this->config->application->formsDir,
            'Chell\Messages'                => APP_PATH . $this->config->application->messagesDir,
            'Chell\Models'                  => APP_PATH . $this->config->application->modelsDir,
            'Chell\Models\Kodi'             => APP_PATH . $this->config->application->modelsDir . 'kodi/',
            'Chell\Plugins'                 => APP_PATH . $this->config->application->pluginsDir,
            'Duo'                           => APP_PATH . $this->config->application->vendorDir . 'duo/'
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
    private function setSession($config)
    {
        $this->di->setShared('session', function () use ($config) {
            $session = new Manager();
            $adapter = null;

            if($config->redis->enabled){
                $adapter = new Redis(new AdapterFactory(new SerializerFactory()), [
                   'host'   => $config->redis->host,
                   'port'   => $config->redis->port,
                   'index'  => '1',
                   'auth'   => $config->redis->auth
               ]);
            }
            else {
                $savePath = ini_get('session.save_path');
                $adapter = new Stream(['savePath' => $savePath]);
            }

            $session->setAdapter($adapter);
            $session->setName(ini_get('session.name') . '_' . str_replace(' ', '_', $config->application->title));
            $session->start();

            return $session;
        });
    }

    /**
     * Sets the static assets files, such as JS and CSS.
     */
    private function setAssets()
    {
        $version = $this->config->application->version;

        $this->application->assets->collection('header')->addCss('css/default/default.' . ($this->config->application->debug ? null : 'min.') . 'css', true, false, array(), $version, true);

        foreach($this->jsFiles as $collection => $files){
            if($this->config->application->debug){
                foreach($files as $file){
                    $this->application->assets->collection($collection)->addJs('js/' . $file, true, false, array('defer' => 'defer'), $version, true);
                }
            }
            else {
                $this->application->assets->collection($collection)->addJs('js/' . $collection . '.min.js', true, false, array('defer' => 'defer'), $version, true);
            }
        }
    }

    /**
     * Sets the title for the application.
     */
    private function setTitle()
    {
        $this->application->tag->setTitle($this->config->application->title);
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