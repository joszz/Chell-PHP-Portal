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
        $command = escapeshellcmd('vsure ' . $config->verisure->user . ' ' . $config->verisure->password . ' armstate');
        $output = shell_exec($command);
        return json_decode($output);
    }

    public static function GetOverview($config, $decode)
    {
        $command = escapeshellcmd('vsure ' . $config->verisure->user . ' ' . $config->verisure->password . ' overview');
        $output = shell_exec($command);
        $overview = json_decode($output);

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
        $command = escapeshellcmd('vsure ' . $config->verisure->user . ' ' . $config->verisure->password . ' eventlog');
        $output = shell_exec($command);
        $log = json_decode($output);

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
}