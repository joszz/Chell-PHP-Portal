<?php
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

    public function __construct()
    {
        define('APP_PATH', realpath('..') . '/');

        $this->di = new FactoryDefault();
        $this->di->set('config', $this->config = new ConfigIni(APP_PATH . 'app/config/config.ini'));

        $this->setDisplayErrors();
        $this->title = $this->config->application->title;
        $this->registerDirs();
        $this->setDB();
        $this->setViewProvider();
        $this->setURLProvider();
        $this->setSession();
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
        $config = $this->config;
        $this->di->set('db', function() use ($config){
            return new DbAdapter(array(
                'host'     => $this->config->database->host,
                'username' => $this->config->database->username,
                'password' => $this->config->database->password,
                'dbname'   => $this->config->database->name
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

    private function setCSSCollection($application)
    {
        $application->assets
                    ->collection('header')
                    ->setTargetPath('css/final.css')
                    ->setTargetUri('css/final.css')
                    ->addCss('css/default.css')
                    ->addCss('css/bootstrap.css')
                    ->addCss('css/bootstrap-theme.css')
                    ->addCss('css/fancybox/jquery.fancybox.css')
                    ->join(true)
                    ->addFilter(new Cssmin())
                    ;
    }

    private function setJSCollection($application)
    {
        $application->assets
                    ->collection('footer')
                    ->setTargetPath('js/final.js')
                    ->setTargetUri('js/final.js')
                    ->addJs('js/jquery-2.1.4.js')
                    ->addJs('js/fancybox/jquery.fancybox.js')
                    ->addJs('js/bootstrap.js')
                    ->addJs('js/jquery.shorten.js')
                    ->addJs('js/jquery.vibrate.js')
                    ->addJs('js/default.js')
                    ->join(true)
                    ->addFilter(new Jsmin());
    }

    private function setMenu($application)
    {
        $application->view->menu = Menus::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => 1),
        ));
    }

    private function setTitle($application)
    {
        $application->tag->setTitle($application->view->title = $this->title);
    }

    public function tostring()
    {
        $application = new Application($this->di);
        $this->setCSSCollection($application);
        $this->setJSCollection($application);
        $this->setMenu($application);
        $this->setTitle($application);

        return $application->handle()->getContent();
    }
}