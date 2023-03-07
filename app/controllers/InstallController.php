<?php

namespace Chell\Controllers;

use PDO;
use Chell\Models\Users;
use Chell\Models\Settings;
use Phalcon\Config\Config;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Migrations\Migrations;
use WriteiniFile\WriteiniFile;

/**
 * The controller responsible for the installation.
 *
 * @package Controllers
 */
class InstallController extends BaseController
{
    private $postedData;

    private $tableOrder = ['devices', 'menu_items', 'users', 'menu_items_users', 'settings', 'snmp_hosts', 'snmp_records', 'speedtest', 'widget_position'];

    /**
     * Add the install CSS file to the head.
     */
    public function initialize()
    {
        parent::initialize();
        $this->assets->addStyle('install');
        $this->assets->addScript('toggle-passwords');
    }

    /**
     * Shows the form to setup Chell.
     * Also shows if the extensions required are enabled.
     */
    public function indexAction()
    {
        $this->ensureDirectories();
        $this->view->containerFullHeight = true;
        $this->view->mbstringEnabled = extension_loaded('mbstring');
        $this->view->phalconEnabled = extension_loaded('phalcon');
        $this->view->pdoEnabled = extension_loaded('pdo');
        $this->view->pdoMysqlEnabled = extension_loaded('pdo_mysql');
        $this->view->gdEnabled = extension_loaded('gd');
        $this->view->curlEnabled = extension_loaded('curl');
        $this->view->snmpEnabled = extension_loaded('snmp');
        $this->view->permissions = array(
            'Logs directory'            => is_writable(APP_PATH . 'app/logs'),
            'config.ini'                => is_writable(APP_PATH . 'app/config/config.ini'),
            'Install controller'        => is_writable(APP_PATH . 'app/controllers/InstallController.php'),
            'Install views'             => is_writable(APP_PATH . 'app/views/install'),
            'DB structure file'         => is_writable(APP_PATH . 'sql/db-structure.sql'),
            'Image cache directory'     => is_writable(PUBLIC_PATH . 'img/cache')
        );

        setcookie('username', null, -1, BASEPATH, null, true, true);
        setcookie('password', null, -1, BASEPATH, null, true, true);
    }

    /**
     * Does the actuall install. Redirects back to the root on success.
     */
    public function goAction()
    {
        $this->postedData = $config = $this->request->getPost();

        $this->createDatabase();

        $version = $this->settings->application->version;
        //Migration version doesn't exist, grab latest version that exists from the migration folder.
        if (!is_dir(__DIR__ . '/../migrations/' . $version))
        {
            $allMigrations = glob(__DIR__ . '/../migrations/*', GLOB_ONLYDIR);
            rsort($allMigrations);
            $version = basename($allMigrations[0]);
        }

        $migration = new Migrations();
        $migrationOptions = [
            'migrationsDir' => [
                __DIR__ . '/../migrations',
            ],
            'directory' => __DIR__ . '/../',
            'version' => $version,
            'config' => new Config([
                'database' => [
                    'adapter' => 'mysql',
                    'host' => $config['mysql-host'],
                    'username' => $config['chell-database-user'],
                    'password' => $config['chell-database-password'],
                    'dbname' => $config['chell-database'],
                    'charset' => 'utf8',
                ]
            ])
        ];
        
        foreach($this->tableOrder as $table)
        {
            $migrationOptions['tableName'] = $table;
            $migration::run($migrationOptions);

            if ($this->tableOrder[count($this->tableOrder)-1] != $table)
            {
                $migration::removeCurrentVersion($migrationOptions, $migrationOptions['version']);
            }
        }

        $this->di->set('db', function() use ($config) {
            return new DbAdapter([
                'host'     => $config['mysql-host'],
                'username' => $config['chell-database-user'],
                'password' => $config['chell-database-password'],
                'dbname'   => $config['chell-database'],
                'charset'  => 'utf8'
            ]);
        });

        $this->createAdminUser();
        (new Settings(['name' => 'phalcon_crypt_key', 'section' => 'general', 'category' => 'application', 'value' => bin2hex(random_bytes(32))]))->save();
        $this->writeConfig();
        $this->cleanup();

        $this->response->redirect('');
    }

    /**
     * Make sure the essential directories exits, if not create them.
     */
    private function ensureDirectories()
    {
        if (!is_dir(APP_PATH . 'app/logs'))
        {
            mkdir(APP_PATH . 'app/logs', 0660);
        }

        if (!is_dir(PUBLIC_PATH . 'img/cache'))
        {
            mkdir(PUBLIC_PATH . 'img/cache', 0660);
        }

        if (!is_dir(PUBLIC_PATH . 'img/icons/menu'))
        {
            mkdir(PUBLIC_PATH . 'img/icons/menu', 0660);
        }
    }

    /**
     * Creates the DB if it doesn't exist yet using the MySQL root user.
     * Grants access to the MySQL user, specified in the form to be used with Chell, for the created DB
     */
    private function createDatabase()
    {
        $connection = new PDO('mysql:host=' . $this->postedData['mysql-host'], 'root', $this->postedData['root-password']);
        $connection->exec('CREATE DATABASE IF NOT EXISTS ' . $this->postedData['chell-database']);
        $connection->exec('CREATE USER IF NOT EXISTS \'' . $this->postedData['chell-database-user'] . '\'@\'' . $this->postedData['mysql-host'] . '\' IDENTIFIED WITH mysql_native_password BY \'' . $this->postedData['chell-database-password'] . '\'');
        $connection->exec('GRANT DELETE, SELECT, INSERT, UPDATE, CREATE, REFERENCES, ALTER on ' . $this->postedData['chell-database'] . '.* TO ' . $this->postedData['chell-database-user'] . '@' . $this->postedData['mysql-host']);
        $connection  = null;
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
     * Writes the settings specified in the form to config.ini.
     */
    private function writeConfig()
    {
        $config = [
            'general' => [
                'debug' => '0',
                'installed' => '1'
            ],
            'database' => [
                'host' => $this->postedData['mysql-host'],
                'name' => $this->postedData['chell-database'],
                'username' => $this->postedData['chell-database-user'],
                'password' => $this->postedData['chell-database-password'],
            ]
        ];
        (new WriteiniFile(APP_PATH . 'app/config/config.ini'))->create($config)->write();
    }

    /**
     * Cleans up the files for install. Silently fails (when for example insufficient privileges.
     */
    private function cleanup()
    {
        @unlink(APP_PATH . 'app/controllers/InstallController.php');
        @unlink(APP_PATH . 'app/views/install/index.phtml');
        @rmdir(APP_PATH . 'app/views/install/');
    }
}
