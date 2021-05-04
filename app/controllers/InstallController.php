<?php

namespace Chell\Controllers;

use Chell\Models\Users;
use Chell\Models\Menus;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

/**
 * The controller responsible for the installation.
 *
 * @package Controllers
 */
class InstallController extends BaseController
{
    private $dbStructureFilename = APP_PATH . 'db-structure.sql';
    private $postedData;

    /**
     * Add the install CSS file to the head.
     */
    public function initialize()
    {
        die;
        parent::initialize();
        $this->assets->collection('header')->addCss('css/default/install.min.css', true, false, [], $this->config->application->version, true);
    }

    /**
     * Shows the form to setup Chell.
     * Also shows if the extensions required are enabled.
     */
    public function indexAction()
    {
        $this->ensureLogDirectory();
        $this->view->containerFullHeight = true;
        $this->view->mbstringEnabled = extension_loaded('mbstring');
        $this->view->psrEnabled = extension_loaded('psr');
        $this->view->phalconEnabled = extension_loaded('phalcon');
        $this->view->pdoEnabled = extension_loaded('pdo');
        $this->view->pdoMysqlEnabled = extension_loaded('pdo_mysql');
        $this->view->permissions = array(
            'Logs directory'            => is_writable(APP_PATH . 'app/logs'),
            'Image cache directory'     => is_writable(APP_PATH . 'public/img/cache'),
            'config.ini'                => is_writable(APP_PATH . 'app/config/config.ini'),
            'Install controller'        => is_writable(APP_PATH . 'app/controllers/InstallController.php'),
            'Install views'             => is_writable(APP_PATH . 'app/views/install'),
            'DB structure file'         => is_writable(APP_PATH . 'db-structure.sql')
        );
    }

    /**
     * Does the actuall install. Redirects back to the root on success.
     */
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

    /**
     * Make sure the logs directory exists, if not create one.
     */
    private function ensureLogDirectory()
    {
        if (!is_dir(APP_PATH . 'app/logs'))
        {
            mkdir(APP_PATH . 'app/logs', 0660);
        }
    }

    /**
     * Creates the DB if it doesn't exist yet using the MySQL root user.
     * Grants access to the MySQL user, specified in the form to be used with Chell, for the created DB
     */
    private function createDatabase()
    {
        $connection = new \PDO('mysql:host=' . $this->postedData['mysql-host'], 'root', $this->postedData['root-password']);
        $connection->exec('CREATE DATABASE IF NOT EXISTS ' . $this->postedData['chell-database']);
        $connection->exec('GRANT DELETE, SELECT, INSERT, UPDATE on ' . $this->postedData['chell-database'] . '.* TO ' . $this->postedData['chell-user'] . '@' . $this->postedData['mysql-host']);
        $connection  = null;
    }

    /**
     * Uses the db structure file to create the DB structure for Chell.
     */
    private function createDatabaseStructure()
    {
        $connection = new \PDO('mysql:dbname=' . $this->postedData['chell-database'] . ';host=' . $this->postedData['mysql-host'], 'root', $this->postedData['root-password']);
        $connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);
        $connection->exec(file_get_contents($this->dbStructureFilename));
        $connection = null;
    }

    /**
     * Creates the specified user, sets the specified password and hashes the password and then saves the user to the DB.
     */
    private function createAdminUser()
    {
        $user = new Users(['username' => $this->postedData['chell-user']]);
        $user->password = $this->security->hash($this->postedData['chell-password']);
        $user->save();
    }

    /**
     * Cretaes the default menu.
     */
    private function createDefaultMenu()
    {
        $menu = new Menus(['id' => 1, 'name' => 'default']);
        $menu->save();
    }

    /**
     * Writes the settings specified in the form to config.ini.
     */
    private function writeConfig()
    {
        $this->config->database->host = $this->postedData['mysql-host'];
        $this->config->database->name = $this->postedData['chell-database'];
        $this->config->database->username = $this->postedData['chell-database-user'];
        $this->config->database->password = $this->postedData['chell-database-password'];
        $this->config->application->phalconCryptKey = bin2hex(random_bytes(32));

        $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
    }

    /**
     * Cleans up the files for install. Silently fails (when for example insufficient privileges.
     */
    private function cleanup()
    {
        @unlink($this->dbStructureFilename);
        @unlink(APP_PATH . 'app/controllers/InstallController.php');
        @array_map('unlink', array_filter(glob(APP_PATH . 'app/views/install/')));
        @unlink(APP_PATH . 'app/views/install/');
    }
}