<?php

namespace Chell;

use Chell\Controllers\ErrorController;
use Chell\Exceptions\ChellException;
use Chell\Plugins\SecurityPlugin;
use Chell\Plugins\LicenseStamper;
use Chell\Messages\TranslatorWrapper;

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
    private $js = array('vendor/jquery-3.4.1.js',
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
                        'vendor/waves.js',
                        'vendor/md5.js',
                        'default.js',
                        'dashboard-blocks/devices.js');

    private $css = array('vendor/jquery.fancybox.css',
                         'vendor/waves.css',
                         'vendor/bootstrap-select.css',
                         'vendor/bootstrap-toggle.css',
                         'vendor/jquery.bootstrap-touchspin.css',
                         'default/default.css');

    /**
     * Initialize Phalcon.
     */
    public function __construct()
    {
        $executionTime = -microtime(true);
        define('APP_PATH', realpath('..') . '/');

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
        $this->setTranslator();
    }

    /**
     * function defined for PHP's set_exception_handler.
     *
     * @param \Throwable $exception  The exception being thrown.
     */
    public function exceptionHandler(\Throwable $exception)
    {
        require_once(APP_PATH . 'app/controllers/ErrorController.php');

        new ErrorController(new ChellException($exception), $this->config);
    }

    /**
     * Adds necessary JS to JS array based upon what's enabled in config.
     */
    private function setJSBasedOnEnabledBlocks()
    {
        if ($this->config->phpsysinfo->enabled)
        {
            $this->js[] = 'dashboard-blocks/phpsysinfo.js';
        }

        if ($this->config->transmission->enabled)
        {
            $this->js[] = 'dashboard-blocks/transmission.js';
        }

        if ($this->config->sickrage->enabled)
        {
            $this->js[] = 'dashboard-blocks/sickrage.js';
        }

        if ($this->config->couchpotato->enabled)
        {
            $this->js[] = 'dashboard-blocks/couchpotato.js';
        }

        if ($this->config->kodi->enabled || $this->config->subsonic->enabled)
        {
            $this->js[] = 'dashboard-blocks/nowplaying.js';
        }

        if ($this->config->kodi->enabled || $this->config->couchpotato->enabled)
        {
            $this->js[] = 'dashboard-blocks/gallery.js';
        }

        if ($this->config->hypervadmin->enabled)
        {
            $this->js[] = 'dashboard-blocks/hyperv-admin.js';
        }

        if ($this->config->motion->enabled)
        {
            $this->js[] = 'dashboard-blocks/motion.js';
        }

        if ($this->config->speedtest->telemetry != 'off')
        {
            $this->js[] = 'vendor/chartist/chartist.js';
            $this->js[] = 'vendor/chartist/chartist-plugin-legend.js';
        }

        if ($this->config->speedtest->enabled)
        {
            $this->js[] = 'dashboard-blocks/speedtest.js';
        }

        if ($this->config->youless->enabled)
        {
            $this->js[] = 'dashboard-blocks/youless.js';
        }

        if ($this->config->opcache->enabled)
        {
            $this->js[] = 'dashboard-blocks/opcache.js';
        }

        if ($this->config->pihole->enabled)
        {
            $this->js[] = 'dashboard-blocks/pihole.js';
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
            'Chell\Messages'    => APP_PATH . 'app/messages/',
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

        if (!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('header')
                 ->setTargetPath($finalFile)
                 ->setTargetUri($finalFile);
        }

        if ($this->config->application->debug || !file_exists(getcwd() . '/' . $finalFile))
        {
            if (!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalFile, '/css/compressed/final_*.css');
                $this->application->assets->collection('header')->join(true)->addFilter(new Cssmin());
                //todo: stamps for all files, so will appear multiple times in minified output.
                //->addFilter(new LicenseStamper());
            }

            foreach ($this->css as $css)
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

        if (!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('footer')
                 ->setTargetPath($finalDefaultFile)
                 ->setTargetUri($finalDefaultFile);
        }

        if ($this->config->application->debug || !file_exists(getcwd() . '/' . $finalDefaultFile))
        {
            if (!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalDefaultFile, '/js/compressed/default_*.min.js');
                $this->application->assets->collection('footer')->join(true)->addFilter(new Jsmin())->addFilter(new LicenseStamper());
            }

            foreach ($this->js as $js)
            {
                $this->application->assets->collection('footer')->addJs('js/' . $js, true, false, array('defer' => 'defer'));
            }
        }
        else
        {
            $this->application->assets->collection('footer')->addJs($finalDefaultFile, true, false, array('defer' => 'defer'));
        }

        //Dashboard file
        $mtimeHash = $this->createMTimeHash(array('dashboard.js'), getcwd() . '/js/');
        $finalDashboardFile = 'js/compressed/dashboard_' . $mtimeHash . '.min.js';

        if (!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('dashboard')
                 ->setTargetPath($finalDashboardFile)
                 ->setTargetUri($finalDashboardFile);
        }

        if ($this->config->application->debug || !file_exists(getcwd() . '/' . $finalDashboardFile))
        {
            $dashJS = $this->application->assets->collection('dashboard')->addJs('js/dashboard.js', true, false, array('defer' => 'defer'));

            if (!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalDashboardFile, '/js/compressed/dashboard_*.min.js');
                $dashJS->join(true)->addFilter(new Jsmin())->addFilter(new LicenseStamper());
            }
        }
        else
        {
            $this->application->assets->collection('dashboard')->addJs($finalDashboardFile, true, false, array('defer' => 'defer'));
        }

        //Settings file
        $mtimeHash = $this->createMTimeHash(array('settings.js'), getcwd() . '/js/');
        $finalSettingsFile = 'js/compressed/settings_' . $mtimeHash . '.min.js';

        if (!$this->config->application->debug)
        {
            $this->application->assets
                 ->collection('settings')
                 ->setTargetPath($finalSettingsFile)
                 ->setTargetUri($finalSettingsFile);
        }

        if ($this->config->application->debug || !file_exists(getcwd() . '/' . $finalSettingsFile))
        {
            $settingsJS = $this->application->assets->collection('settings')->addJs('js/settings.js', true, false, array('defer' => 'defer'));

            if (!$this->config->application->debug)
            {
                $this->cleanupCompressedFiles($finalSettingsFile, '/js/compressed/settings_*.min.js');
                $settingsJS->join(true)->addFilter(new Jsmin())->addFilter(new LicenseStamper());
            }
        }
        else
        {
            $this->application->assets->collection('settings')->addJs($finalSettingsFile, true, false, array('defer' => 'defer'));
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

        if (($key = array_search(getcwd() . '/' . $finalFile, $files)) !== false)
        {
            unset($files[$key]);
        }

        if (count($files))
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

        foreach ($files as $file)
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
    public function tostring()
    {
        return $this->application->handle()->getContent();
    }
}