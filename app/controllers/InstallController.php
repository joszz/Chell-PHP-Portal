<?php

namespace Chell\Controllers;

use Chell\Models\Users;

class InstallController extends BaseController
{
    private $dbStructureFilename = APP_PATH . 'db-structure.sql';
    private $dbConnection;

    public function indexAction()
    {

    }

    public function doInstallAction()
    {
        $data = $this->request->getPost();
        $dbName = $this->config->database->name;
        $dbName = 'test';

        $this->createDatabase($data['user'], $data['password'], $dbName);
        $this->createDatabaseStructure($data['user'], $data['password'], $dbName);
        $this->createAdminUser();
        $this->cleanup();

        mysqli_close($this->dbConnection);
    }

    private function createDatabase($user, $password, $dbName)
    {
        $connection = mysqli_connect($this->config->database->host, $user, $password);
        mysqli_query($connection, 'CREATE DATABASE IF NOT EXISTS ' . $dbName) or die(mysqli_error($connection));
        mysqli_query($connection, 'GRANT DELETE, SELECT, INSERT, UPDATE on ' . $dbName . '.* TO ' . $this->config->database->username . '@localhost') or die(mysqli_error($connection));
        mysqli_close($connection);
    }

    private function createDatabaseStructure($user, $password, $dbName)
    {
        $this->dbConnection = mysqli_connect($this->config->database->host, $user, $password, $dbName);
        mysqli_multi_query($this->dbConnection, file_get_contents($this->dbStructureFilename)) or die(mysqli_error($this->dbConnection));
    }

    private function createAdminUser()
    {
        $user = new Users([
            'username' => 'admin',
        ]);

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