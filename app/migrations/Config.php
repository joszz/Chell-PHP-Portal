<?php

use Phalcon\Config\Config;

$config = parse_ini_file(__DIR__ .'/../config/config.ini', true);
return new Config([
    'database' => [
        'adapter'   => 'mysql',
        'host'      => $config['database']['host'],
        'username'  => $config['database']['username'],
        'password'  => $config['database']['password'],
        'dbname'    => $config['database']['name'],
        'charset'   => 'utf8',
    ],
    'application' => [
        'logInDb'       => true,
        'migrationsDir' => __DIR__ . './'
    ]
]);