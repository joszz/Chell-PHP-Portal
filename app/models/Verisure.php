<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to Verisure.
 *
 * @see https://github.com/persandstrom/python-verisure
 * @package Models
 */
class Verisure extends Model
{
    public static $eventToReadableName = [
        'DOORWINDOW_STATE_CLOSED' => 'Closed',
        'DOORWINDOW_STATE_OPENED' => 'Opened'
    ];

    /**
     * Gets the current arm state of the alarm.
     *
     * @param object $config	The config object representing config.ini.
     * @return string           JSON ecnoded output of the command.
     */
    public static function GetArmState($config)
    {
        return self::executeCommand('armstate', $config);
    }

    /**
     * Gets the overview of the system with the most general information.
     *
     * @param object $config	The config object representing config.ini.
     * @param boolean $encode   Whether or not to JSON encode the output of the overview command.
     * @return object|string    Either an JSON encoded string when $encode == true, or an object.
     */
    public static function GetOverview($config, $encode)
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

        return $encode ? json_encode($overview) : $overview;
    }

    /**
     * Retrieves the current log records.
     *
     * @param object $config	The config object representing config.ini.
     * @return object           An objectwith all eventLogItems in it.
     */
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

    /**
     * Executes the vsure python library on the commandline and retrieves the output from the Verisure API.
     *
     * @param string $command   The command to execute on the Verisure API.
     * @param object $config	The config object representing config.ini.
     * @return object           An object JSON decoded from the output of the Verisure API.
     */
    private static function executeCommand($command, $config)
    {
        $command = escapeshellcmd('vsure ' . $config->verisure->username . ' ' . $config->verisure->password . ' ' . $command);
        $output = shell_exec($command);
        return json_decode($output);
    }
}