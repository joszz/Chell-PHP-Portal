<?php

namespace Chell;

use Chell\Controllers\ErrorController;
use Chell\Exceptions\ChellException;
use Chell\Plugins\SecurityPlugin;
use Chell\Plugins\LicenseStamper;

use Phalcon\Crypt;
use Phalcon\Loader;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Assets\Filters\Jsmin;
use Phalcon\Assets\Filters\Cssmin;

/**
 * Frontcontroller sets up Phalcon to run the application.
 */
class FrontController
{
    private $title;
    private $config;
    private $di;
    private $application;
    private $js = array('jquery-3.2.1.js',
                        'jquery.fancybox.js',
                        'bootstrap.js',
                        'bootstrap-select/bootstrap-select.js',
                        'bootstrap-tabcollapse.js',
                        'bootstrap-toggle.js',
                        'jquery.bootstrap-touchspin.js',
                        'jquery.vibrate.js',
                        'jquery.tinytimer.js',
                        'jquery.isloading.js',
                        'jquery.fullscreen.js',
                        'jquery.mobile-events.js',
                        'waves.js',
                        'md5.js',
                        'default.js',
                        'dashboard-blocks/devices.js',
                        'dashboard-blocks/phpsysinfo.js');

    private $css = array('bootstrap.css',
                         'font-awesome/font-awesome.css',
                         'jquery.fancybox.css',
                         'waves.css',
                         'bootstrap-select.css',
                         'bootstrap-toggle.css',
                         'jquery.bootstrap-touchspin.css',
                         'default.css');

    /**
     * Initialize Phalcon.
     */
    public function __construct()
    {
        $executionTime = -microtime(true);
        define('APP_PATH', realpath('..') . '/');

        set_error_handler(array(&$this, 'errorHandler'), -1 & ~E_NOTICE & ~E_USER_NOTICE);
        set_exception_handler(array(&$this, 'exceptionHandler'));

        $this->config = $config = new ConfigIni(APP_PATH . 'app/config/config.ini');
        $this->di = new FactoryDefault();
        $this->di->set('config', $config);

        $this->setJSBasedOnEnabledBlocks();
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

        $this->setCSSCollection();
        $this->setJSCollection();
        $this->setTitle();
    }

    /**
     * function defined for PHP's set_error_handler.
     *
     * @todo finish this
     * @param int       $errno      The level of the error raised
     * @param string    $errstr     The error message.
     * @param string    $errfile    The filename that the error was raised in
     * @param int       $errline    The line number the error was raised at
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        require_once(APP_PATH . 'app/controllers/ErrorController.php');
        new ErrorController(new ChellException($errstr, $errno, $errline, $errfile));
    }

    /**
     * function defined for PHP's set_exception_handler.
     *
     * @todo finish this
     * @param Exception $exception  The exception being thrown.
     */
    public function exceptionHandler($exception)
    {
        require_once(APP_PATH . 'app/controllers/ErrorController.php');
        new ErrorController($exception);
    }

    /**
     * Adds necessary JS to JS array based upon what's enabled in config.
     */
    private function setJSBasedOnEnabledBlocks(){
        if($this->config->transmission->enabled) {
            $this->js[] = 'dashboard-blocks/transmission.js';
        }
        if($this->config->sickrage->enabled) {
            $this->js[] = 'dashboard-blocks/sickrage.js';
        }
        if($this->config->couchpotato->enabled) {
            $this->js[] = 'dashboard-blocks/couchpotato.js';
        }

        if($this->config->kodi->enabled || $this->config->subsonic->enabled) {
            $this->js[] = 'dashboard-blocks/nowplaying.js';
        }

        if($this->config->kodi->enabled || $this->config->couchpotato->enabled){
            $this->js[] = 'dashboard-blocks/gallery.js';
        }
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
            'Chell\Exceptions'  => APP_PATH . 'app/exceptions/',
            'Chell\Forms'       => APP_PATH . $this->config->application->formsDir,
            'Chell\Models'      => APP_PATH . $this->config->application->modelsDir,
            'Chell\Models\Kodi' => APP_PATH . $this->config->application->modelsDir . 'kodi/',
            'Chell\Plugins'     => APP_PATH . $this->config->application->pluginsDir,
            'Duo'               => APP_PATH . 'app/duo/',
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
                'dbname'   => $config->database->name
            ));
        });

        $this->di->set('dbKodiMusic', function() use ($config) {
            return new DbAdapter(array(
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->kodiMusic
            ));
        });

        $this->di->set('dbKodiVideo', function() use ($config) {
            return new DbAdapter(array(
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->kodiVideo
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
        $this->di->set('session', function () {
            $session = new Session();
            $session->start();

            return $session;
        });
    }

    /**
     * Create and compress CSS collection using $this->css as base.
     */
    private function setCSSCollection()
    {
        $mtimeHash = $this->createMTimeHash($this->css, getcwd() . '/css/');
        $finalFile = 'css/compressed/final_' . $mtimeHash . '.css';

        if(!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('header')
                 ->setTargetPath($finalFile)
                 ->setTargetUri($finalFile);
        }

        if($this->config->application->debug || !file_exists(getcwd() . '/' . $finalFile))
        {
            if(!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalFile, '/css/compressed//final_*.css');
                $this->application->assets->collection('header')->join(true)->addFilter(new Cssmin())->addFilter(new LicenseStamper());
            }

            foreach($this->css as $css)
            {
                $this->application->assets->collection('header')->addCss('css/' . $css);
            }
        }
        else
        {
            $this->application->assets->collection('header')->addCss($finalFile);
        }
    }

    /**
     * Create and compress JS collections using $this->js as base.
     */
    private function setJSCollection()
    {
        $mtimeHash = $this->createMTimeHash($this->js, getcwd() . '/js/');
        $finalDefaultFile = 'js/compressed/default_' . $mtimeHash . '.min.js';

        if(!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('footer')
                 ->setTargetPath($finalDefaultFile)
                 ->setTargetUri($finalDefaultFile);
        }

        if($this->config->application->debug || !file_exists(getcwd() . '/' . $finalDefaultFile))
        {
            if(!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalDefaultFile, '/js/compressed/default_*.min.js');
                $this->application->assets->collection('footer')->join(true)->addFilter(new Jsmin())->addFilter(new LicenseStamper());
            }

            foreach($this->js as $js)
            {
                $this->application->assets->collection('footer')->addJs('js/' . $js);
            }
        }
        else
        {
            $this->application->assets->collection('footer')->addJs($finalDefaultFile);
        }

        //Dashboard file
        $mtimeHash = $this->createMTimeHash(array('dashboard.js'), getcwd() . '/js/');
        $finalDashboardFile = 'js/compressed/dashboard_' . $mtimeHash . '.min.js';

        if(!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('dashboard')
                 ->setTargetPath($finalDashboardFile)
                 ->setTargetUri($finalDashboardFile);
        }

        if($this->config->application->debug || !file_exists(getcwd() . '/' . $finalDashboardFile))
        {
            $dashJS = $this->application->assets->collection('dashboard')->addJs('js/dashboard.js');

            if(!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalDashboardFile, '/js/compressed/dashboard_*.min.js');
                $dashJS->join(true)->addFilter(new Jsmin())->addFilter(new LicenseStamper());
            }
        }
        else
        {
            $this->application->assets->collection('dashboard')->addJs($finalDashboardFile);
        }

        //Settings file
        $mtimeHash = $this->createMTimeHash(array('settings.js'), getcwd() . '/js/');
        $finalSettingsFile = 'js/compressed/settings_' . $mtimeHash . '.min.js';

        if(!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('settings')
                 ->setTargetPath($finalSettingsFile)
                 ->setTargetUri($finalSettingsFile);
        }

        if($this->config->application->debug || !file_exists(getcwd() . '/' . $finalSettingsFile))
        {
            $settingsJS = $this->application->assets->collection('settings')->addJs('js/settings.js');

            if(!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalSettingsFile, '/js/compressed/settings_*.min.js');
                $settingsJS->join(true)->addFilter(new Jsmin())->addFilter(new LicenseStamper());
            }
        }
        else
        {
            $this->application->assets->collection('settings')->addJs($finalSettingsFile);
        }
    }

    /**
     * Cleanup absolete compressed files.
     *
     * @param mixed $finalFile      The new compressed file
     * @param mixed $pattern        The pattern to match against for files in directory.
     */
    private function cleanupCompressedFiles($finalFile, $pattern)
    {
        $files = glob(getcwd() . $pattern);

        if(($key = array_search(getcwd() . '/' . $finalFile, $files)) !== false)
        {
            unset($files[$key]);
        }

        if(count($files))
        {
            array_map('unlink', $files);
        }
    }

    /**
     * Creates a filemtime string from all files in array and then create a hash out of it.
     *
     * @param array $files      The array of files to create the hash for based on their modified time.
     * @param mixed $basepath   The path where the files in the array can be found.
     * @return string           The hash based on the string of filemtimes.
     */
    private function createMTimeHash(array $files, $basepath)
    {
        $mtimes = 0;

        foreach($files as $file)
        {
            $mtimes += filemtime($basepath . $file);
        }

        return md5($mtimes);
    }

    /**
     * Sets the title for the application.
     */
    private function setTitle()
    {
        $this->application->tag->setTitle($this->application->view->title = $this->title);
    }

    /**
     * Echoes the HTML to the browser.
     * @return mixed    The complete HTML of the request.
     */
    public function tostring()
    {
        return $this->application->handle()->getContent();
    }
}