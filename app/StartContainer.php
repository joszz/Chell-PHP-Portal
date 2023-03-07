<?php

use Phalcon\Migrations\Migrations;
use Phalcon\Config\Config;
use Chell\Models\SettingsContainer;

require_once(__DIR__ . '/models/SettingsContainer.php');
require_once(__DIR__ . '/../vendor/autoload.php');
$config = parse_ini_file(__DIR__ . '/config/config.ini', true);

//Only run migrations if initial setup is done
if ($config['general']['installed'])
{
    $migration = new Migrations();
    $migrationOptions = [
        'migrationsDir' => [
            __DIR__ . '/migrations',
        ],
        'directory' => __DIR__ . '/',
        'version' => SettingsContainer::getMigrationVersion(),
        'config' => new Config([
            'database' => [
                'adapter' => 'mysql',
                'host' => $config['database']['host'],
                'username' => $config['database']['username'],
                'password' => $config['database']['password'],
                'dbname' => $config['database']['name'],
                'charset' => 'utf8',
            ]
        ])
    ];
    $migration::run($migrationOptions);
}

?>