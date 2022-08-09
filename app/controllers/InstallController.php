<?php

namespace Chell\Controllers;

use PDO;
use Chell\Models\Users;
use Chell\Models\Settings;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use WriteiniFile\WriteiniFile;

/**
 * The controller responsible for the installation.
 *
 * @package Controllers
 */
class InstallController extends BaseController
{
    private string $dbStructureFilename = APP_PATH . 'sql/db-structure.sql';
    private $postedData;

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
        $this->view->psrEnabled = extension_loaded('psr');
        $this->view->phalconEnabled = extension_loaded('phalcon');
        $this->view->pdoEnabled = extension_loaded('pdo');
        $this->view->pdoMysqlEnabled = extension_loaded('pdo_mysql');
        $this->view->gdEnabled = extension_loaded('gd');
        $this->view->curlEnabled = extension_loaded('curl');
        $this->view->snmpEnabled = extension_loaded('snmp');
        $this->view->permissions = array(
            'Logs directory'            => is_writable(APP_PATH . 'app/logs'),
            'Image cache directory'     => is_writable(APP_PATH . 'img/cache'),
            'config.ini'                => is_writable(APP_PATH . 'app/config/config.ini'),
            'Install controller'        => is_writable(APP_PATH . 'app/controllers/InstallController.php'),
            'Install views'             => is_writable(APP_PATH . 'app/views/install'),
            'DB structure file'         => is_writable(APP_PATH . 'sql/db-structure.sql')
        );
    }

    /**
     * Does the actuall install. Redirects back to the root on success.
     */
    public function goAction()
    {
        $this->postedData = $config = $this->request->getPost();

        $this->createDatabase();
        $this->createDatabaseStructure();

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
        $this->createDefaultSettings();
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

        if (!is_dir(APP_PATH . 'img/cache'))
        {
            mkdir(APP_PATH . 'img/cache', 0660);
        }

        if (!is_dir(APP_PATH . 'img/icons/menu'))
        {
            mkdir(APP_PATH . 'img/icons/menu', 0660);
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
        $connection->exec('CREATE USER \'' . $this->postedData['chell-database-user'] . '\'@\'' . $this->postedData['mysql-host'] . '\' IDENTIFIED WITH mysql_native_password BY \'' . $this->postedData['chell-database-password'] . '\'');
        $connection->exec('GRANT DELETE, SELECT, INSERT, UPDATE on ' . $this->postedData['chell-database'] . '.* TO ' . $this->postedData['chell-database-user'] . '@' . $this->postedData['mysql-host']);
        $connection  = null;
    }

    /**
     * Uses the db structure file to create the DB structure for Chell.
     */
    private function createDatabaseStructure()
    {
        $connection = new PDO('mysql:dbname=' . $this->postedData['chell-database'] . ';host=' . $this->postedData['mysql-host'], 'root', $this->postedData['root-password']);
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
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
     * Creates all default setting records.
     */
    private function createDefaultSettings()
    {
        //General
        $this->createDefaultSetting('title', 'general', 'application', 'Chell PHP Portal');
        $this->createDefaultSetting('background', 'general', 'application', 'autobg');
        $this->createDefaultSetting('background_latitude', 'general', 'application', '');
        $this->createDefaultSetting('background_longitude', 'general', 'application', '');
        $this->createDefaultSetting('alert_timeout', 'general', 'application', '5');
        $this->createDefaultSetting('items_per_page', 'general', 'application', '10');
        $this->createDefaultSetting('phalcon_crypt_key', 'general', 'application', bin2hex(random_bytes(32)));
        $this->createDefaultSetting('demo_mode', 'general', 'application', '0');
        $this->createDefaultSetting('check_device_states_interval', 'general', 'application', '10');
        $this->createDefaultSetting('check_now_playing_interval', 'general', 'application', '10');
        //Redis
        $this->createDefaultSetting('enabled', 'general', 'redis', '0');
        $this->createDefaultSetting('host', 'general', 'redis', 'localhost');
        $this->createDefaultSetting('port', 'general', 'redis', '6379');
        $this->createDefaultSetting('auth', 'general', 'redis', '');
        //Imageproxy
        $this->createDefaultSetting('enabled', 'general', 'imageproxy', '0');
        $this->createDefaultSetting('url', 'general', 'imageproxy', '');
        //HIBP
        $this->createDefaultSetting('enabled', 'general', 'hibp', '0');
        //Speedtest
        $this->createDefaultSetting('enabled', 'dashboard', 'speedtest', '0');
        $this->createDefaultSetting('test_order', 'dashboard', 'speedtest', 'IPDU');
        $this->createDefaultSetting('time_upload', 'dashboard', 'speedtest', '10');
        $this->createDefaultSetting('time_download', 'dashboard', 'speedtest', '10');
        $this->createDefaultSetting('get_isp_info', 'dashboard', 'speedtest', '0');
        $this->createDefaultSetting('get_isp_distance', 'dashboard', 'speedtest', 'km');
        $this->createDefaultSetting('telemetry', 'dashboard', 'speedtest', 'full');
        $this->createDefaultSetting('ip_info_url', 'dashboard', 'speedtest', 'https://ipinfo.io/');
        $this->createDefaultSetting('ip_info_token', 'dashboard', 'speedtest', '');
        $this->createDefaultSetting('what_is_my_browser_api_key', 'dashboard', 'speedtest', '');
        $this->createDefaultSetting('what_is_my_browser_api_url', 'dashboard', 'speedtest', 'https://api.whatismybrowser.com/api/v2/');
        //Couchpotato
        $this->createDefaultSetting('enabled', 'dashboard', 'couchpotato', '0');
        $this->createDefaultSetting('url', 'dashboard', 'couchpotato', '');
        $this->createDefaultSetting('api_key', 'dashboard', 'couchpotato', '');
        $this->createDefaultSetting('rotate_interval', 'dashboard', 'couchpotato', '');
        $this->createDefaultSetting('tmdb_api_url', 'dashboard', 'couchpotato', 'https://api.themoviedb.org/3/');
        $this->createDefaultSetting('tmdb_api_key', 'dashboard', 'couchpotato', '');
        //Broadcast
        $this->createDefaultSetting('broadcast', 'dashboard', 'network', '');
        //PHPSysInfo
        $this->createDefaultSetting('enabled', 'dashboard', 'phpsysinfo', '0');
        $this->createDefaultSetting('url', 'dashboard', 'phpsysinfo', '');
        $this->createDefaultSetting('username', 'dashboard', 'phpsysinfo', '');
        $this->createDefaultSetting('password', 'dashboard', 'phpsysinfo', '');
        //CPU
        $this->createDefaultSetting('enabled', 'dashboard', 'cpu', '0');
        //Transmission
        $this->createDefaultSetting('enabled', 'dashboard', 'transmission', '0');
        $this->createDefaultSetting('username', 'dashboard', 'transmission', '');
        $this->createDefaultSetting('password', 'dashboard', 'transmission', '');
        $this->createDefaultSetting('url', 'dashboard', 'transmission', '');
        $this->createDefaultSetting('update_interval', 'dashboard', 'transmission', '10');
        //Subsonic
        $this->createDefaultSetting('enabled', 'dashboard', 'subsonic', '0');
        $this->createDefaultSetting('url', 'dashboard', 'subsonic', '');
        $this->createDefaultSetting('username', 'dashboard', 'subsonic', '');
        $this->createDefaultSetting('password', 'dashboard', 'subsonic', '');
        //Kodi
        $this->createDefaultSetting('enabled', 'dashboard', 'kodi', '0');
        $this->createDefaultSetting('url', 'dashboard', 'kodi', '');
        $this->createDefaultSetting('username', 'dashboard', 'kodi', '');
        $this->createDefaultSetting('password', 'dashboard', 'kodi', '');
        $this->createDefaultSetting('rotate_movies_interval', 'dashboard', 'kodi', '30');
        $this->createDefaultSetting('rotate_episodes_interval', 'dashboard', 'kodi', '30');
        $this->createDefaultSetting('rotate_albums_interval', 'dashboard', 'kodi', '30');
        $this->createDefaultSetting('dbvideo', 'dashboard', 'kodi', 'MyVideos116');
        $this->createDefaultSetting('dbmusic', 'dashboard', 'kodi', 'MyMusic72');
        $this->createDefaultSetting('dbhost', 'dashboard', 'kodi', '');
        $this->createDefaultSetting('dbuser', 'dashboard', 'kodi', '');
        $this->createDefaultSetting('dbpassword', 'dashboard', 'kodi', '');
        //Sickrage
        $this->createDefaultSetting('enabled', 'dashboard', 'sickrage', '0');
        $this->createDefaultSetting('url', 'dashboard', 'sickrage', '');
        $this->createDefaultSetting('api_key', 'dashboard', 'sickrage', '');
        $this->createDefaultSetting('enabled', 'dashboard', 'sickrage', '');
        //Duo
        $this->createDefaultSetting('enabled', 'dashboard', 'duo', '0');
        $this->createDefaultSetting('ikey', 'dashboard', 'duo', '');
        $this->createDefaultSetting('skey', 'dashboard', 'duo', '');
        $this->createDefaultSetting('akey', 'dashboard', 'duo', '');
        $this->createDefaultSetting('api_hostname', 'dashboard', 'duo', '');
        //Motion
        $this->createDefaultSetting('enabled', 'dashboard', 'motion', '0');
        $this->createDefaultSetting('url', 'dashboard', 'motion', '');
        $this->createDefaultSetting('picture_path', 'dashboard', 'motion', '');
        $this->createDefaultSetting('update_interval', 'dashboard', 'motion', '30');
        //Opcache
        $this->createDefaultSetting('enabled', 'dashboard', 'opcache', '0');
        //Pihole
        $this->createDefaultSetting('enabled', 'dashboard', 'pihole', '0');
        $this->createDefaultSetting('url', 'dashboard', 'pihole', '');
        //Youless
        $this->createDefaultSetting('enabled', 'dashboard', 'youless', '0');
        $this->createDefaultSetting('url', 'dashboard', 'youless', '');
        $this->createDefaultSetting('password', 'dashboard', 'youless', '');
        $this->createDefaultSetting('update_interval', 'dashboard', 'youless', '5');
        $this->createDefaultSetting('threshold_primary', 'dashboard', 'youless', '250');
        $this->createDefaultSetting('threshold_warning', 'dashboard', 'youless', '500');
        $this->createDefaultSetting('threshold_danger', 'dashboard', 'youless', '1000');
        //SNMP
        $this->createDefaultSetting('enabled', 'dashboard', 'snmp', '0');
        $this->createDefaultSetting('update_interval', 'dashboard', 'snmp', '5');
        //Verisure
        $this->createDefaultSetting('enabled', 'dashboard', 'verisure', '0');
        $this->createDefaultSetting('username', 'dashboard', 'verisure', '');
        $this->createDefaultSetting('password', 'dashboard', 'verisure', '');
        $this->createDefaultSetting('update_interval', 'dashboard', 'verisure', '180');
        $this->createDefaultSetting('securitycode', 'dashboard', 'verisure', '');
        //Roborock
        $this->createDefaultSetting('enabled', 'dashboard', 'roborock', '0');
        $this->createDefaultSetting('ip', 'dashboard', 'roborock', '');
        $this->createDefaultSetting('token', 'dashboard', 'roborock', '');
        $this->createDefaultSetting('update_interval', 'dashboard', 'roborock', '30');
        //Jellyfin
        $this->createDefaultSetting('enabled', 'dashboard', 'jellyfin', '0');
        $this->createDefaultSetting('url', 'dashboard', 'jellyfin', '');
        $this->createDefaultSetting('token', 'dashboard', 'jellyfin', '');
        $this->createDefaultSetting('userid', 'dashboard', 'jellyfin', '');
        $this->createDefaultSetting('views', 'dashboard', 'jellyfin', '');
        $this->createDefaultSetting('rotate_interval', 'dashboard', 'jellyfin', '30');
        //Pulseway
        $this->createDefaultSetting('enabled', 'dashboard', 'pulseway', '0');
        $this->createDefaultSetting('url', 'dashboard', 'pulseway', 'https://api.pulseway.com/v2/');
        $this->createDefaultSetting('username', 'dashboard', 'pulseway', '');
        $this->createDefaultSetting('password', 'dashboard', 'pulseway', '');
        $this->createDefaultSetting('systems', 'dashboard', 'pulseway', '');
        $this->createDefaultSetting('update_interval', 'dashboard', 'pulseway', '30');
        //Database stats
        $this->createDefaultSetting('enabled', 'dashboard', 'databasestats', '0');
        $this->createDefaultSetting('update_interval', 'dashboard', 'databasestats', '30');
        //Apache
        $this->createDefaultSetting('enabled', 'dashboard', 'apache', '0');
        $this->createDefaultSetting('server_status_url', 'dashboard', 'apache', '');
        $this->createDefaultSetting('fpm_status_url', 'dashboard', 'apache', '');
        //Docker
        $this->createDefaultSetting('enabled', 'dashboard', 'docker', '0');
        $this->createDefaultSetting('update_interval', 'dashboard', 'docker', '30');
        //Disks
        $this->createDefaultSetting('enabled', 'dashboard', 'disks', '0');
        $this->createDefaultSetting('update_interval', 'dashboard', 'disks', '30');
        //Tdarr
        $this->createDefaultSetting('enabled', 'dashboard', 'tdarr', '0');
        $this->createDefaultSetting('url', 'dashboard', 'tdarr', '');
        $this->createDefaultSetting('update_interval', 'dashboard', 'tdarr', '30');
        //Sonarr
        $this->createDefaultSetting('enabled', 'dashboard', 'sonarr', '0');
        $this->createDefaultSetting('url', 'dashboard', 'sonarr', '');
        $this->createDefaultSetting('api_key', 'dashboard', 'sonarr', '30');
    }

    /**
     * Creates a setting with specified values, and saves it to the database.
     *
     * @param mixed $name       The name of the setting.
     * @param mixed $section    The section of the setting.
     * @param mixed $category   The category of the setting.
     * @param mixed $value      The value of the setting.
     */
    private function createDefaultSetting($name, $section, $category, $value)
    {
        $setting = new Settings();
        $setting->name = $name;
        $setting->section = $section;
        $setting->category = $category;
        $setting->value = $value;
        $setting->save();
    }

    /**
     * Writes the settings specified in the form to config.ini.
     */
    private function writeConfig()
    {
        $config = [
            'general' => [ 'debug' => '0'],
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
        @unlink($this->dbStructureFilename);
        @unlink(APP_PATH . 'app/controllers/InstallController.php');
        @array_map('unlink', array_filter(glob(APP_PATH . 'app/views/install/')));
        @unlink(APP_PATH . 'app/views/install/');
    }
}
