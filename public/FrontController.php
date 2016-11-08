<?php

use Phalcon\Crypt;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Assets\Filters\Jsmin;
use Phalcon\Assets\Filters\Cssmin;

/**
 * Frontcontroller initializes Phalcon to run the application.
 */
class FrontController
{
    private $title;
    private $config;
    private $di;
    private $application;
    private $js = array('jquery-3.1.1.js',
                        'fancybox/jquery.fancybox.js',
                        //'fancybox/jquery.fancybox-buttons.js',
                        'bootstrap.js',
                        'bootstrap-select/bootstrap-select.js',
                        'bootstrap-tabcollapse.js',
                        //'responsive-paginate.js',
                        //'jquery.shorten.js',
                        'jquery.vibrate.js',
                        'jquery.tinytimer.js',
                        'jquery.isloading.js',
                        'waves.js',
                        'md5.js',
                        'default.js',
                        'dashboard-blocks/devices.js',
                        'dashboard-blocks/gallery.js',
                        'dashboard-blocks/phpsysinfo.js',
                        'dashboard-blocks/transmission.js',
                        'dashboard-blocks/nowplaying.js',
                        );

    private $css = array('bootstrap.css',
                         'bootstrap-theme.css',
                         //'font-awesome.css',
                         'fancybox/jquery.fancybox.css',
                         //'fancybox/jquery.fancybox-buttons.css',
                         'waves.css',
                         'bootstrap-select.css',
                         'default.css');

    /**
     * Initialize Phalcon .
     */
    public function __construct()
    {
        $executionTime = -microtime(true);
        define('APP_PATH', realpath('..') . '/');
        $this->config = $config = new ConfigIni(APP_PATH . 'app/config/config.ini');

        $this->di = new FactoryDefault();
        $this->di->set('config', $config);

        $this->di->set('dispatcher', function () {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);

            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });

        $this->di->set('crypt', function() use ($config) {
            $crypt = new Crypt();
            $crypt->setKey($config->application->phalconCryptKey);
            return $crypt;
        });

        $this->setDisplayErrors();
        $this->title = $config->application->title;
        $this->registerDirs();
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
     * Show errors in browser, decided by flag in config.
     */
    private function setDisplayErrors()
    {
        ini_set('display_errors', $this->config->application->debug ? 'on' : 'off');
    }

    /**
     * Register all directories used by Phalcon.
     */
    private function registerDirs()
    {
        $loader = new Loader();
        $loader->registerDirs(
            array(
                APP_PATH . $this->config->application->controllersDir,
                APP_PATH . $this->config->application->pluginsDir,
                APP_PATH . $this->config->application->libraryDir,
                APP_PATH . $this->config->application->modelsDir,
                APP_PATH . $this->config->application->modelsDir . 'kodi/',
                APP_PATH . $this->config->application->formsDir,
            )
        )->register();
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

        $this->application->assets
                          ->collection('header')
                          ->setTargetPath($finalFile)
                          ->setTargetUri($finalFile);

        if(!file_exists(getcwd() . '/' . $finalFile))
        {
            $this->cleanupCompressedFiles($finalFile, '/css/compressed//final_*.css');
            $this->application->assets->collection('header')->join(true)->addFilter(new Cssmin());

            foreach($this->css as $css) $this->application->assets->collection('header')->addCss('css/' . $css);
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

        $this->cleanupCompressedFiles($finalDefaultFile, '/js/compressed/default_*.min.js');

        $this->application->assets
             ->collection('footer')
             ->setTargetPath($finalDefaultFile)
             ->setTargetUri($finalDefaultFile);

        if(!file_exists(getcwd() . '/' . $finalDefaultFile))
        {
            $this->application->assets->collection('footer')->join(true)->addFilter(new Jsmin());
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

        $this->cleanupCompressedFiles($finalDashboardFile, '/js/compressed/dashboard_*.min.js');

        $this->application->assets
             ->collection('dashboard')
             ->setTargetPath($finalDashboardFile)
             ->setTargetUri($finalDashboardFile);

        if(!file_exists(getcwd() . '/' . $finalDashboardFile))
        {
            $this->application->assets->collection('dashboard')
                 ->addJs('js/dashboard.js')
                 ->join(true)
                 ->addFilter(new Jsmin());
        }
        else
        {
            $this->application->assets->collection('dashboard')->addJs($finalDashboardFile);
        }

        //Settings file
        $mtimeHash = $this->createMTimeHash(array('settings.js'), getcwd() . '/js/');
        $finalSettingsFile = 'js/compressed/settings_' . $mtimeHash . '.min.js';

        $this->cleanupCompressedFiles($finalSettingsFile, '/js/compressed/settings_*.min.js');

        $this->application->assets
             ->collection('settings')
             ->setTargetPath($finalSettingsFile)
             ->setTargetUri($finalSettingsFile);

        if(!file_exists(getcwd() . '/' . $finalSettingsFile))
        {
            $this->application->assets->collection('settings')
                 ->addJs('js/settings.js')
                 ->join(true)
                 ->addFilter(new Jsmin());
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