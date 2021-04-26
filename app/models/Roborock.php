<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to Jellyfin.
 *
 * @see https://github.com/rytilahti/python-miio
 * @package Models
 */
class Roborock extends Model
{
    /**
     * Gets the info stats for the configured Roborock.
     *
     * @param object $config    The configuration file to use.
     * @return string           The pre-formatted info of Roborock.
     */
    public static function GetInfo($config)
    {
        return self::executeCommand('info', $config);
    }

    /**
     * Gets the different stats for the Roborock. Weirdly formatted string provided, so pick the string apart with substrings.
     *
     * @param object $config    The configuration file to use.
     * @return string[]         The various Roborock stats in an associative array.
     */
    public static function GetStatus($config)
    {
        $status = self::executeCommand('status', $config);

        return [
            'state'     => self::getStatusPart($status, 'state='),
            'battery'   => self::getStatusPart($status, 'battery='),
            'fan'       => self::getStatusPart($status, 'fanspeed='),
            'area'      => self::getStatusPart($status, 'clean_area='),
            'time'      => self::getStatusPart($status, 'clean_time='),
        ];
    }

    /**
     * Starts the Roborock's cleaning.
     *
     * @param object $config    The configuration file to use.
     */
    public static function Start($config)
    {
        self::executeCommand('start', $config);
    }

    /**
     * Stops the Roborock's cleaning.
     *
     * @param object $config    The configuration file to use.
     */
    public static function Stop($config)
    {
        self::executeCommand('stop', $config);
    }

    /**
     * Calls the miiocli python API with ip and token arguments as well as the command to run (see --help for more).
     *
     * @param string $command   The command to run on the Roborock.
     * @param object $config    The configuration file to use.
     * @return string           The output of the run command.
     */
    private static function executeCommand($command, $config)
    {
        $command = escapeshellcmd('miiocli vacuum --ip ' . $config->roborock->ip . ' --token ' . $config->roborock->token . ' ' . $command);
        $output = shell_exec($command);
        $output = explode("\n", $output);

        return $output[1];
    }

    /**
     * Used by GetStatus to pick apart the weirdly formatted string provided. Using some ugly substring logic.
     *
     * @param string $haystack      The complete output of the mioocli status command
     * @param string $needle        The statistic to retrieve from the complete output of the miiocli command.
     * @param string $seperator     The seperator to indicate next value of the $haystack. Limiting the substring.
     * @return string               The value for the requested statistic ($needle).
     */
    private static function getStatusPart($haystack, $needle, $seperator = ' ')
    {
        $start = strpos($haystack, $needle) + strlen($needle);
        $end = strpos($haystack, $seperator, $start);
        return substr($haystack, $start, $end - $start);
    }
}