<?php

use Phalcon\Migrations\Migrations;
use Phalcon\Config\Config;

require_once(__DIR__ . '/../vendor/autoload.php');
$config = parse_ini_file(__DIR__ . '/config/config.ini', true);

if ($config['general']['installed'])
{
    ob_start();
    require_once(__DIR__ . '/../package.json');
    $version = json_decode(ob_get_clean())->version;

    $migration = new Migrations();
    $migrationOptions = [
        'migrationsDir' => [
            __DIR__ . '/migrations',
        ],
        'directory' => __DIR__ . '/',
        'version' => $version,
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