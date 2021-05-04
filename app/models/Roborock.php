<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to Jellyfin.
 *
 * @see https://github.com/rytilahti/python-miio
 * @package Models
 */
class Roborock extends BaseModel
{
    /**
     * Gets the info stats for the configured Roborock.
     *
     * @return string           The pre-formatted info of Roborock.
     */
    public function getInfo() : string
    {
        return $this->executeCommand('info', false);
    }

    /**
     * Gets the different stats for the Roborock. Weirdly formatted string provided, so pick the string apart with substrings.
     *
     * @return string[]         The various Roborock stats in an associative array.
     */
    public function getStatus() : array
    {
        $status = $this->executeCommand('status');

        return [
            'state'     => $this->getStatusPart($status, 'state='),
            'battery'   => $this->getStatusPart($status, 'battery='),
            'fan'       => $this->getStatusPart($status, 'fanspeed='),
            'area'      => $this->getStatusPart($status, 'clean_area='),
            'time'      => $this->getStatusPart($status, 'clean_time='),
        ];
    }

    /**
     * Starts the Roborock's cleaning.
     */
    public function start() : string
    {
        return $this->executeCommand('start');
    }

    /**
     * Stops the Roborock's cleaning.
     */
    public function stop() : string
    {
        return $this->executeCommand('stop');
    }

    /**
     * Stops the Roborock's cleaning and returns home.
     */
    public function home() : string
    {
        return $this->executeCommand('home');
    }

    /**
     * Calls the miiocli python API with ip and token arguments as well as the command to run (see --help for more).
     *
     * @param string  $command            The command to run on the Roborock.
     * @param boolean $removeFirstLine    Whether to trim the first line from the output. Defaults to true.
     * @return string                     The output of the run command.
     */
    private function executeCommand($command, $removeFirstLine = true) : string
    {
        $command = escapeshellcmd('miiocli vacuum --ip ' . $this->_config->roborock->ip . ' --token ' . $this->_config->roborock->token . ' ' . $command);
        $output = shell_exec($command);

        if ($removeFirstLine)
        {
            $output = explode("\n", $output);
            array_shift($output);
            return implode($output, "\n");
        }

        return $output;
    }

    /**
     * Used by GetStatus to pick apart the weirdly formatted string provided. Using some ugly substring logic.
     *
     * @param string $haystack      The complete output of the mioocli status command
     * @param string $needle        The statistic to retrieve from the complete output of the miiocli command.
     * @param string $seperator     The seperator to indicate next value of the $haystack. Limiting the substring.
     * @return string               The value for the requested statistic ($needle).
     */
    private function getStatusPart($haystack, $needle, $seperator = ' ') : string
    {
        $start = strpos($haystack, $needle) + strlen($needle);
        $end = strpos($haystack, $seperator, $start);
        return substr($haystack, $start, $end - $start);
    }
}