<?php

namespace Chell\Controllers;

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

use Chell\Models\Users;

class InstallController extends BaseController
{
    private $dbStructureFilename = APP_PATH . 'db-structure.sql';
    private $dbConnection;

    public function indexAction()
    {
        $this->view->mbstringEnabled = extension_loaded('mbstring');
        $this->view->psrEnabled = extension_loaded('psr');
        $this->view->phalconEnabled = extension_loaded('phalcon');
        $this->view->pdoEnabled = extension_loaded('pdo');
        $this->view->pdoMysqlEnabled = extension_loaded('pdo_mysql');
    }

    public function doInstallAction()
    {
        $data = $this->request->getPost();
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

        $this->createDatabase($data['user'], $data['password'], $config->database->name);
        $this->createDatabaseStructure($data['user'], $data['password'], $config->database->name);
        $this->dbConnection = null;
        $this->createAdminUser();
        //$this->cleanup();
    }

    private function createDatabase($user, $password, $dbName)
    {
        $connection = new \PDO('mysql:host=localhost', $user, $password);
        $connection->exec('CREATE DATABASE IF NOT EXISTS ' . $this->config->database->name);
        $connection->exec('GRANT DELETE, SELECT, INSERT, UPDATE on ' . $this->config->database->name . '.* TO ' . $this->config->database->username . '@localhost');
        $connection  = null;
    }

    private function createDatabaseStructure($user, $password, $dbName)
    {
        $this->dbConnection = new \PDO('mysql:dbname=' . $this->config->database->name . ';host=localhost', $user, $password);
        $this->dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);
        $this->dbConnection->exec(file_get_contents($this->dbStructureFilename));
    }

    private function createAdminUser()
    {
        $user = new Users(['username' => 'admin']);
        $user->password = $this->security->hash('admin');
        $user->save();
    }

    private function cleanup()
    {
        unlink($this->dbStructureFilename);
        unlink(APP_PATH . 'app/controllers/InstallController.php');
        unlink(APP_PATH . 'app/views/install/');
    }
}