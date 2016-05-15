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

class FrontController
{
    private $title;
    private $config;
    private $di;
    private $application;
    private $js = array('jquery-2.2.3.js', 
                        'fancybox/jquery.fancybox.js', 
                        'bootstrap.js', 
                        'bootstrap-select/bootstrap-select.js', 
                        'bootstrap-tabcollapse.js', 
                        'responsive-paginate.js', 
                        'jquery.shorten.js', 
                        'jquery.vibrate.js', 
                        'jquery.tinytimer.js',
                        'jquery.isloading.js',
                        'waves.js', 
                        'default.js',
                        'dashboard-blocks/transmission.js',
                        'dashboard-blocks/phpsysinfo.js',
                        );
    private $css = array('bootstrap.css', 'bootstrap-theme.css', 'fancybox/jquery.fancybox.css', 'waves.css', 'bootstrap-select.css', 'default.css');

    public function __construct()
    {
        $executionTime = -microtime(true);
        define('APP_PATH', realpath('..') . '/');

        $this->di = new FactoryDefault();
        $this->di->set('config', $this->config = new ConfigIni(APP_PATH . 'app/config/config.ini'));

        $this->di->set('dispatcher', function () {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);

            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });

        $this->di->set('crypt', function() {
            $crypt = new Crypt();
            $crypt->setKey($this->config->application->phalconCryptKey);
            return $crypt;
        });

        $this->setDisplayErrors();
        $this->title = $this->config->application->title;
        $this->registerDirs();
        $this->setDB();
        $this->setViewProvider();
        $this->setURLProvider();
        $this->setSession();

        $this->application = new Application($this->di);
        $this->application->view->executionTime = $executionTime;

        $this->setCSSCollection();
        $this->setJSCollection();
        $this->setTitle();
    }

    private function setDisplayErrors()
    {
        ini_set('display_errors', $this->config->application->debug ? 'on' : 'off');
    }
    
    private function registerDirs()
    {
        $loader = new Loader();
        $loader->registerDirs(
            array(
                APP_PATH . $this->config->application->controllersDir,
                APP_PATH . $this->config->application->pluginsDir,
                APP_PATH . $this->config->application->libraryDir,
                APP_PATH . $this->config->application->modelsDir,
                APP_PATH . $this->config->application->formsDir,
            )
        )->register();
    }

    // Setup the database service
    private function setDB()
    {
        $this->di->set('db', function() {
            return new DbAdapter(array(
                'host'     => $this->config->database->host,
                'username' => $this->config->database->username,
                'password' => $this->config->database->password,
                'dbname'   => $this->config->database->name
            ));
        });

        $this->di->set('dbKodiMusic', function() {
            return new DbAdapter(array(
                'host'     => $this->config->database->host,
                'username' => $this->config->database->username,
                'password' => $this->config->database->password,
                'dbname'   => $this->config->database->kodiMusic
            ));
        });

        $this->di->set('dbKodiVideo', function() {
            return new DbAdapter(array(
                'host'     => $this->config->database->host,
                'username' => $this->config->database->username,
                'password' => $this->config->database->password,
                'dbname'   => $this->config->database->kodiVideo
            ));
        });
    }

    private function setViewProvider()
    {
        $config = $this->config;
        $this->di->set('view', function () use ($config) {
            $view = new View();
            $view->setViewsDir(APP_PATH . $this->config->application->viewsDir);
            return $view;
        });
    }

    private function setURLProvider()
    {
        $config = $this->config;
        $this->di->set('url', function () use ($config) {
            $url = new UrlProvider();
            $url->setBaseUri($this->config->application->baseUri);
            return $url;
        });
    }

    private function setSession()
    {
        $this->di->set('session', function () {
            $session = new Session();
            $session->start();

            return $session;
        });
    }

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

    private function createMTimeHash(array $files, $basepath)
    {
        $mtimes = 0;
        
        foreach($files as $file)
        {
            $mtimes += filemtime($basepath . $file);
        }

        return md5($mtimes);
    }

    private function setTitle()
    {
        $this->application->tag->setTitle($this->application->view->title = $this->title);
    }

    public function tostring()
    {
        return $this->application->handle()->getContent();
    }
}