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
    public const waterFlowLevels = [
        "Minimum",
        "Low",
        "High",
        "Maximum"
    ];

    public const fanspeedLevels = [
        101 => "Quiet",
        102 => "Balanced",
        103 => "Turbo",
        104 => "Max",
    ];

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
        $fanSpeed = $this->getStatusPart($status, 'fanspeed=');

        return [
            'state'             => $this->getStatusPart($status, 'state='),
            'battery'           => $this->getStatusPart($status, 'battery='),
            'fan'               => self::fanspeedLevels[$fanSpeed],
            'area'              => $this->getStatusPart($status, 'clean_area='),
            'time'              => $this->getStatusPart($status, 'clean_time='),
            'waterbox_attached' => strtolower($this->getStatusPart($status, 'is_water_box_attached=')),
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
     * Gets the current set fan speed.
     *
     * @return string   The fan speed as fanspeedLevels
     */
    public function getFanSpeed() : string
    {
        return trim($this->executeCommand('fan_speed'));
    }

    /**
     * Gets the current sound volume
     *
     * @return string   The volume in percentage.
     */
    public function getSoundVolume() : string
    {
        return trim($this->executeCommand('sound_volume'));
    }

    /**
     * Gets the current waterflow level.
     *
     * @return string   The waterflow level as waterFlowLevels
     */
    public function getWaterflow() : string
    {
        return trim(str_replace('WaterFlow.', '', $this->executeCommand('waterflow')));
    }

    /**
     * Sets the fan speed
     *
     * @param int $speed  The speed, as defined by fanspeedLevels, to set.
     */
    public function setFanSpeed(int $speed)
    {
       $this->executeCommand('set_fan_speed ' . $speed);
    }

    /**
     * Sets the sound volume
     *
     * @param int $volume     The volume to set.
     */
    public function setSoundVolume(int $volume)
    {
        $this->executeCommand('set_sound_volume ' . $volume);
    }

    /**
     * Sets the waterflow level.
     *
     * @param string $waterflow  The waterflow level, as defined by waterFlowLevels, to set.
     */
    public function setWaterflow(string $waterflow)
    {
        $this->executeCommand('set_waterflow ' . $waterflow);
    }

    /**
     * Calls the miiocli python API with ip and token arguments as well as the command to run (see --help for more).
     *
     * @param string  $command            The command to run on the Roborock.
     * @param boolean $removeFirstLine    Whether to trim the first line from the output. Defaults to true.
     * @return string                     The output of the run command.
     */
    private function executeCommand(string $command, bool $removeFirstLine = true) : string
    {
        $output = shell_exec('miiocli vacuum --ip ' . escapeshellcmd($this->_settings->roborock->ip) . ' --token ' . escapeshellcmd($this->_settings->roborock->token) . ' ' . $command);

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
    private function getStatusPart(string $haystack, string $needle, string $seperator = ' ') : string
    {
        $start = strpos($haystack, $needle) + strlen($needle);
        $end = strpos($haystack, $seperator, $start);
        return substr($haystack, $start, $end - $start);
    }
}