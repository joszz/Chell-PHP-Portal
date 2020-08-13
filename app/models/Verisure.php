<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class Verisure extends Model
{
    public static $eventToReadableName = [
        'DOORWINDOW_STATE_CLOSED' => 'Closed',
        'DOORWINDOW_STATE_OPENED' => 'Opened'
    ];

    public static function GetArmState($config)
    {
        return self::executeCommand('armstate', $config);
    }

    public static function GetOverview($config, $decode)
    {
        $overview = self::executeCommand('overview', $config);

        foreach($overview->climateValues as $value)
        {
            if($value->temperature >= 25){
                $value->cssClass = 'text-danger';
            }
            else if($value->temperature >= 20 && $value->temperature < 25){
                $value->cssClass = 'text-warning';
            }
            else if($value->temperature >= 10 && $value->temperature < 20){
                $value->cssClass = 'text-success';
            }
            else {
                $value->cssClass = 'text-primary';
            }
        }

        return $decode ? $overview : json_encode($overview);
    }

    public static function GetLog($config)
    {
        $log = self::executeCommand('eventlog', $config);

        foreach ($log->eventLogItems as $logItem)
        {
            if (array_key_exists($logItem->eventCategory, self::$eventToReadableName))
            {
                $logItem->eventCategory = self::$eventToReadableName[$logItem->eventCategory];
            }
            else
            {
                $logItem->eventCategory = ucfirst(strtolower($logItem->eventCategory));
            }
        }

        return $log;
    }

    private static function executeCommand($command, $config)
    {
        $command = escapeshellcmd('vsure ' . $config->verisure->username . ' ' . $config->verisure->password . ' ' . $command);
        $output = shell_exec($command);
        return json_decode($output);
    }
}