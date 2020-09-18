<?php

namespace Chell\Controllers;

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

use Chell\Models\Users;
use Chell\Models\Menus;

class InstallController extends BaseController
{
    private $dbStructureFilename = APP_PATH . 'db-structure.sql';
    private $postedData;

    public function initialize()
    {
        parent::initialize();
        $this->assets->collection('header')->addCss('css/default/install.css', true, false, [], $this->config->application->version, true);
    }

    public function indexAction()
    {
        $this->view->containerFullHeight = true;
        $this->view->mbstringEnabled = extension_loaded('mbstring');
        $this->view->psrEnabled = extension_loaded('psr');
        $this->view->phalconEnabled = extension_loaded('phalcon');
        $this->view->pdoEnabled = extension_loaded('pdo');
        $this->view->pdoMysqlEnabled = extension_loaded('pdo_mysql');
    }

    public function goAction()
    {
        $this->postedData = $this->request->getPost();
        $config = $this->postedData;

        $this->di->set('db', function() use ($config) {
            return new DbAdapter([
                'host'     => $config['mysql-host'],
                'username' => $config['chell-database-user'],
                'password' => $config['chell-database-password'],
                'dbname'   => $config['chell-database'],
                'charset'  => 'utf8'
            ]);
        });

        $this->createDatabase();
        $this->createDatabaseStructure();
        $this->createAdminUser();
        $this->createDefaultMenu();
        $this->writeConfig();
        $this->cleanup();

        header('Location: ' . $this->config->application->baseUri);
    }

    private function createDatabase()
    {
        $connection = new \PDO('mysql:host=' . $this->postedData['mysql-host'], 'root', $this->postedData['root-password']);
        $connection->exec('CREATE DATABASE IF NOT EXISTS ' . $this->postedData['chell-database']);
        $connection->exec('GRANT DELETE, SELECT, INSERT, UPDATE on ' . $this->postedData['chell-database'] . '.* TO ' . $this->postedData['chell-user'] . '@' . $this->postedData['mysql-host']);
        $connection  = null;
    }

    private function createDatabaseStructure()
    {
        $connection = new \PDO('mysql:dbname=' . $this->postedData['chell-database'] . ';host=' . $this->postedData['mysql-host'], 'root', $this->postedData['root-password']);
        $connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);
        $connection->exec(file_get_contents($this->dbStructureFilename));
        $connection = null;
    }

    private function createAdminUser()
    {
        $user = new Users(['username' => $this->postedData['chell-user']]);
        $user->password = $this->security->hash($this->postedData['chell-password']);
        $user->save();
    }

    private function createDefaultMenu()
    {
        $menu = new Menus(['id' => 1, 'name' => 'default']);
        $menu->save();
    }

    private function writeConfig()
    {
        $this->config->database->host = $this->postedData['mysql-host'];
        $this->config->database->name = $this->postedData['chell-database'];
        $this->config->database->username = $this->postedData['chell-database-user'];
        $this->config->database->password = $this->postedData['chell-database-password'];
        $this->config->application->phalconCryptKey = bin2hex(random_bytes(32));

        $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
    }

    private function cleanup()
    {
        @unlink($this->dbStructureFilename);
        @unlink(APP_PATH . 'app/controllers/InstallController.php');
        @array_map('unlink', array_filter(glob(APP_PATH . 'app/views/install/')));
        @unlink(APP_PATH . 'app/views/install/');
    }
}