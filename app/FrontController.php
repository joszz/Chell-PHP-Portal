<?php

namespace Chell;

use Throwable;

use Chell\Controllers\ErrorController;
use Chell\Exceptions\ChellException;
use Chell\Messages\TranslatorWrapper;
use Chell\Models\Settings;
use Chell\Models\SettingsCategory;
use Chell\Models\SettingsContainer;
use Chell\Plugins\SecurityPlugin;

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
use Phalcon\Debug\Dump;

/**
 * Frontcontroller sets up Phalcon to run the application.
 */
class FrontController
{
    private SettingsContainer $settings;
    private FactoryDefault $di;
    private Application $application;

    private array $jsFiles = [
        'vendor/jquery/jquery.js',
        'vendor/fancybox/jquery.fancybox.js',
        'vendor/bootstrap-sass/assets/javascripts/bootstrap.js',
        'vendor/bootstrap-select/js/bootstrap-select.js',
        'vendor/bootstrap-tabcollapse/bootstrap-tabcollapse.js',
        'vendor/bootstrap-toggle/js/bootstrap-toggle.js',
        'vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.js',
        'vendor/jquery.vibrate/jquery.vibrate.js',
        'vendor/tinytimer/jquery.tinytimer.js',
        'vendor/jquery.isloading/jquery.isloading.js',
        'vendor/jquery-fullscreen-plugin/jquery.fullscreen.js',
        'vendor/chartist/dist/chartist.js',
        'vendor/chartist-plugin-legend/chartist-plugin-legend.js',
        'vendor/waves/waves.js',
        'vendor/spark-md5/spark-md5.js',
        'js/toggle-passwords.js',
        'js/general.js',
    ];

    private array $cssFiles = [
        'vendor/fancybox/jquery.fancybox.css',
        'vendor/waves/waves.css',
        'vendor/bootstrap-select/css/bootstrap-select.css',
        'vendor/bootstrap-toggle/css/bootstrap-toggle.css',
        'vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.css',
        'vendor/bootstrap-toggle/css/bootstrap-toggle.css',
        'css/default/default.css'
    ];

    /**
     * Initialize Phalcon.
     */
    public function __construct()
    {
        $executionTime = -microtime(true);
        define('APP_PATH', realpath('..') . '/');

        $this->di = new FactoryDefault();
        $config = new ConfigIni(APP_PATH . 'app/config/config.ini');
        define('DEBUG', $config->debug);

        $this->registerNamespaces();
        $this->setDB($config);
        $this->settings = $settings = $this->setSettings();
        set_exception_handler([&$this, 'ExceptionHandler']);

        $this->di->set('dispatcher', function () {
            $eventsManager = new EventsManager();
            $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);

            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('Chell\Controllers');

            return $dispatcher;
        });

        $this->di->set('crypt', function() use ($settings) {
            $crypt = new Crypt();
            $crypt->setKey($settings->application->phalcon_crypt_key);
            return $crypt;
        });

        $this->setDisplayErrors();
        $this->setViewProvider();
        $this->setURLProvider($settings);
        $this->setSession($settings);

        $this->application = new Application($this->di);
        $this->application->view->executionTime = $executionTime;

        $this->setAssets();
        $this->setTitle();
        $this->setTranslator();

        function dump($dump)
        {
            if (DEBUG)
            {
                die((new Dump())->variable($dump));
            }
        }
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

        new ErrorController(new ChellException($exception), $this->settings);
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
            'Duo'                               => APP_PATH . 'app/vendor/duo/',
            'Davidearl\WebAuthn'                => APP_PATH . 'app/vendor/WebAuthn/',
            'CBOR'                              => APP_PATH . 'app/vendor/CBOR/',
            'phpseclib'                         => APP_PATH . 'app/vendor/phpseclib/'
        ])->register();
    }

    /**
     * Setup the database services.
     *
     * @param ConfigIni $config	The config object representing config.ini.
     */
    private function setDB(ConfigIni $config)
    {
        $this->di->set('db', function() use ($config) {
            return new DbAdapter([
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->name,
                'charset'  => 'utf8'
            ]);
        });

        $this->di->set('dbKodiMusic', function() use ($config) {
            return new DbAdapter([
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->kodiMusic,
                'charset'  => 'utf8'
            ]);
        });

        $this->di->set('dbKodiVideo', function() use ($config) {
            return new DbAdapter([
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->kodiVideo,
                'charset'  => 'utf8'
            ]);
        });
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
     * Setup Phalcon URL provider.
     */
    private function setURLProvider($settings)
    {
        $this->di->set('url', function () use ($settings) {
            $url = new UrlProvider();
            $url->setBaseUri($settings->application->base_uri);
            return $url;
        });
    }

    /**
     * Instantiate session.
     *
     * @param SettingsContainer $settings    The settings object representing all settings in the database.
     */
    private function setSession(SettingsContainer $settings)
    {
        $this->di->setShared('session', function () use ($settings) {
            $session = new Manager();
            $adapter = null;

            if ($settings->redis->enabled)
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
            $this->application->assets->collection('header')->addCss('css/default/bundle.min.css', true, false, [], $version, true);
            $this->application->assets->collection('general')->addJs('js/general.min.js', true, false, ['defer' => 'defer'], $version, true);
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
     * 
     * @return SettingsContainer    The main settings object to be used throughout the application
     */
    private function setSettings()
    {
        $settings = Settings::find(['order' => 'category']);
        $structuredSettings = new SettingsContainer();

        foreach ($settings as $setting)
        {
            if (!isset($structuredSettings->{$setting->category}))
            {
                $structuredSettings->addCategory(new SettingsCategory($setting->section, $setting->category));
            }

            $structuredSettings->{$setting->category}->addSetting($setting);
        }

        $this->di->set('settings', $structuredSettings);
        return $structuredSettings;
    }

    /**
     * Echoes the HTML to the browser.
     *
     * @return string   The complete HTML of the request.
     */
    public function ToString() : string
    {
        $request = new Request();
        return $this->application->handle(str_replace($this->settings->application->base_uri, '', '/' . $request->getURI()))->getContent();
    }
}

