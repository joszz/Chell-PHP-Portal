<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

class Roborock extends Model
{
    public static function GetInfo($config)
    {
        return self::executeCommand('info', $config);
    }

    public static function GetStatus($config)
    {
        $status = self::executeCommand('status', $config);

        return [
            'state'     => self::getStatusPart($status, 'state='),
            'battery'   => self::getStatusPart($status, 'bat='),
            'fan'       => self::getStatusPart($status, 'fan=', ' '),
            'area'      => self::getStatusPart($status, 'cleaned ', ' '),
            'time'      => self::getStatusPart($status, 'in ', '>'),
        ];
    }

    public static function Start($config)
    {
        self::executeCommand('start', $config);
    }

    public static function Stop($config)
    {
        self::executeCommand('stop', $config);
    }

    private static function executeCommand($command, $config)
    {
        $command = escapeshellcmd('miiocli vacuum --ip ' . $config->roborock->ip . ' --token ' . $config->roborock->token . ' ' . $command);
        $output = shell_exec($command);
        return $output;
    }

    private static function getStatusPart($haystack, $needle, $seperator = ',')
    {
        $start = strpos($haystack, $needle) + strlen($needle);
        $end = strpos($haystack, $seperator, $start);
        return substr($haystack, $start, $end - $start);
    }
}