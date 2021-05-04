<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to Verisure.
 *
 * @see https://github.com/persandstrom/python-verisure
 * @package Models
 */
class Verisure extends BaseModel
{
    public static $eventToReadableName = [
        'DOORWINDOW_STATE_CLOSED' => 'Closed',
        'DOORWINDOW_STATE_OPENED' => 'Opened'
    ];

    public static $statusDisarmedCode = 'DISARMED';
    public static $statusArmedAwayCode = 'ARMED_AWAY';
    public static $statusArmedStayCode = 'ARMED_HOME';

    /**
     * Gets the current arm state of the alarm.
     *
     * @return string           JSON ecnoded output of the command.
     */
    public function getArmState()
    {
        return $this->executeCommand('armstate');
    }

    /**
     * Gets the overview of the system with the most general information.
     *
     * @param boolean $encode   Whether or not to JSON encode the output of the overview command.
     * @return object|string    Either an JSON encoded string when $encode == true, or an object.
     */
    public function getOverview($encode)
    {
        $overview = $this->executeCommand('overview');

        foreach($overview->climateValues as $value)
        {
            if ($value->temperature >= 25)
            {
                $value->cssClass = 'text-danger';
            }
            else if ($value->temperature >= 20 && $value->temperature < 25)
            {
                $value->cssClass = 'text-warning';
            }
            else if ($value->temperature >= 10 && $value->temperature < 20)
            {
                $value->cssClass = 'text-success';
            }
            else
            {
                $value->cssClass = 'text-primary';
            }
        }

        return $encode ? json_encode($overview) : $overview;
    }

    /**
     * Sets the alarm state to the given state.
     *
     * @param string $state      The alarm state to set.
     * @param int $pin           The user pin to use to authenticate.
     */
    public function setArmState($state, $pin)
    {
        $this->executeCommand('set alarm ' . $pin . ' ' . $state);
    }

    /**
     * Retrieves the current log records.
     *
     * @return object           An object with all eventLogItems in it.
     */
    public function getLog()
    {
        $log = $this->executeCommand('eventlog');

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
     * Retrieves all the recorded images for the configured account.
     *
     * @return object           An objectwith all the imageseries in it.
     */
    public function getImageSeries()
    {
        return $this->executeCommand('imageseries');
    }

    /**
     * Calls the Verisure API to retrieve an image and write it to disk. If the file already exists, than return the filename directly.
     *
     * @param string $device_label      The device label.
     * @param string $image_id          The image Id.
     * @param string $capture_time      The capture time.
     * @return string                   The full filepath for the image cached on disk.
     */
    public function getImage($device_label, $image_id, $capture_time) : string
    {
        $filename = APP_PATH  . 'public/img/cache/verisure/' . $capture_time . '.jpg';

        if (!file_exists($filename))
        {
            $this->executeCommand('getimage ' . $device_label .  '  ' . $image_id . ' ' . $filename);
        }

        return $filename;
    }

    /**
     * Calls the Verisure API to capture an image for the device with $device_label.
     *
     * @param string $device_label      The device label.
     * @return object           An object with the JSON encoded output of the API call.
     */
    public function captureImage($device_label)
    {
        return $this->executeCommand('capture ' . $device_label);
    }

    /**
     * Calls the Verisure API to retrieve firmware statistics.
     *
     * @return object           An object with the JSON encoded output of the API call.
     */
    public function getFirmwareStatus()
    {
        return $this->executeCommand('firmware_status');
    }

    /**
     * Executes the vsure python library on the commandline and retrieves the output from the Verisure API.
     *
     * @param string $command   The command to execute on the Verisure API.
     * @return object           An object JSON decoded from the output of the Verisure API.
     */
    private function executeCommand($command)
    {
        $command = escapeshellcmd('vsure ' . $this->_config->verisure->username . ' ' . $this->_config->verisure->password . ' ' . $command);
        $output = shell_exec($command);
        return json_decode($output);
    }
}