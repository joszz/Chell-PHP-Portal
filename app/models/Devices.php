<?php

namespace Chell\Models;

use Chell\Models\Cpu;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 * @suppress PHP2414
 */
class Devices extends BaseModel
{
    /**
     * Sets the database relations
     */
    public function initialize()
    {
        parent::initialize();
        $this->hasMany(
            'id',
            'MenuItems',
            'device_id',
            []
        );
    }

    /**
     * Determines whether a device is awake or not.
     * uses either ADB or ping to determine state.
     *
     * @return bool awake or not.
     */
    public function isDeviceOn() : bool
    {
        if ($this->shutdown_method == 'adb')
        {
            return $this->adbIsAwake();
        }

        return $this->ping() !== false;
    }

    /**
     * Wakes up a device, using either ADB or WOL.
     *
     * @return bool Whether wakeup call succeeded.
     */
    public function wake() : bool
    {
        if ($this->shutdown_method == 'adb')
        {
            return $this->wakeOnAdb();
        }

        return $this->wakeOnLan();
    }

    /**
     * Shuts down a device using either ADB or a Remote Procedure Call.
     *
     * @return bool Whether shutdown command succeeded.
     */
    public function shutdown() : bool
    {
        if ($this->shutdown_method == 'adb')
        {
            return $this->shutdownOnAdb();
        }

        return $this->shutdownOnRpc();
    }

    /**
     * Pings a device and returns the response time.
     *
     * @param int           $ttl    The TimeToLive for the ping request. Defaults to 1 second
     * @return bool|float          The time it took for the device to respond or false if failed.
     */
    private function ping(int $ttl = 10) : bool|float
    {
        $latency = false;
        $ttl     = escapeshellcmd($ttl);
        $host    = escapeshellcmd($this->ip);

        // Exec string for Windows-based systems.
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        {
            // -n = number of pings; -i = ttl.
            $exec_string = 'ping -n 1 -w 1 -i ' . $ttl . ' ' . $host;
        }
        // Exec string for UNIX-based systems (Mac, Linux).
        else
        {
            // -n = numeric output; -c = number of pings; -t = ttl.
            $exec_string = 'ping -n -c 1 -w 1 -t ' . $ttl . ' ' . $host;
        }

        exec($exec_string, $output, $return);

        // Strip empty lines and reorder the indexes from 0 (to make results more uniform across OS versions).
        $output = array_values(array_filter($output));

        // If the result line in the output is not empty, parse it.
        if (!empty($output[1]))
        {
            // Search for a 'time' value in the result line.
            $response = preg_match('/time(?:=|<)(?<time>[\.0-9]+)(?:|\s)ms/', $output[1], $matches);

            // If there's a result and it's greater than 0, return the latency.
            if ($response > 0 && isset($matches['time']))
            {
                $latency = round($matches['time']);
            }
        }

        return $latency;
    }

    /**
     * Uses ADB to determine if an Android device is awake.
     *
     * @return bool Awake or not?
     */
    private function adbIsAwake(int $retryCounter = 0) : bool
    {
        $this->adbConnect();
        $output = strtolower(shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell dumpsys power | grep -e "mWakefulness" | head -1'));

        if (++$retryCounter >= 5 )
        {
            return false;
        }
        else if (empty($output))
        {
            return $this->adbIsAwake($retryCounter);
        }

        return strpos($output, 'asleep') === false && strpos($output, 'dozing') === false;
    }

    /**
     * Wakes up a device by MAC address.
     *
     * @param int       $socket_number  The port to send the magic packet to.
     * @param int       $repetition     The amount of repetition of the MAC in the magic packet. Defaults to 16.
     * @return bool                     Whether or not socket_sendto with magic packet succeeded.
     */
    private function wakeOnLan() : bool
    {
        $hwaddr = pack('H*', preg_replace('/[^0-9a-fA-F]/', '', $this->mac));

        // Create Magic Packet
        $packet = sprintf(
            '%s%s',
            str_repeat(chr(255), 6),
            str_repeat($hwaddr, 16)
        );

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if ($sock !== false) {
            $options = socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);

            if ($options !== false) {
                socket_sendto($sock, $packet, strlen($packet), 0, $this->broadcast, 7);
                socket_close($sock);
                return true;
            }
        }

        return false;
    }

    /**
     * Uses ADB to sent a wakeup keyevent to wake the device.
     *
     * @return bool Always returns true since there is no proper way to determine if the call was successful.
     */
    private function wakeOnAdb() : bool
    {
        $this->adbConnect();
        shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell input keyevent KEYCODE_WAKEUP');
        return true;
    }

    /**
     * Executes a RPC command to shutdown Windows based devices.
     *
     * @see             https://www.howtogeek.com/109655/how-to-remotely-shut-down-or-restart-windows-pcs/
     * @return bool     Whether the shutdown command was successful or not.
     */
    private function shutdownOnRpc() : bool
    {
        $ip = escapeshellcmd($this->ip);
        $user = trim(escapeshellcmd($this->shutdown_user));
        $password = trim(escapeshellcmd($this->shutdown_password));
        $output = [];

        exec('net rpc shutdown -I ' . $ip . ' -U ' . $user . '%' . $password, $output);

        return strpos($output[1], 'succeeded') !== false;
    }

    /**
     * Uses ADB to sent a sleep keyevent to wake the device.
     *
     * @return bool Always returns true since there is no proper way to determine if the call was successful.
     */
    private function shutdownOnAdb() : bool
    {
        $this->adbConnect();
        shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell input keyevent KEYCODE_SLEEP');
        return true;
    }

    /**
     * Checks if device is already connected through ADB. If not, kill the ADB server (to avoid conflicts) and connect to the device.
     * It sometimes happens the ADB connect method doesn't connect, if so try again.
     *
     * @return bool Whether connection was succesfull.
     */
    private function adbConnect() : bool
    {
        $connectedDevices = shell_exec('adb devices -l');

        if (strpos($connectedDevices, $this->ip) === false)
        {
            shell_exec('adb kill-server');
            $output = shell_exec('adb connect ' . escapeshellcmd($this->ip));

            if (strpos($output, 'connected to ' . $this->ip) === false)
            {
                return $this->adbConnect();
            }
        }
        return true;
    }

    /**
     * Retrieves the CPU architecture through ADB.
     *
     * @return bool|string|null   The CPU architecture.
     */
    public function adbGetArchitecture() : bool|string|null
    {
        $this->adbConnect();
        return shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell getprop ro.product.cpu.abi');
    }

    /**
     * Retrieves the battery statistics through ADB.
     *
     * @return string   The battery statistics.
     */
    public function adbGetBatteryStats() : bool|string|null
    {
        $this->adbConnect();
        return shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell dumpsys battery');
    }

    /**
     * Retrieves the current CPU usage through ADB as a percentage.
     *
     * @return float    The current CPU usage.
     * @todo            Copy/paste of devices, refactor
     */
    public function adbGetCpuUsage() : float
    {
        $load = null;
        $this->adbConnect();
        $output = shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell cat /proc/stat');

        // Collect 2 samples - each with 1 second period
        // See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
        $statData1 = Cpu::getServerLoadLinuxData($output);
        sleep(1);
        $output = shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell cat /proc/stat');
        $statData2 = Cpu::getServerLoadLinuxData($output);

        if(!is_null($statData1) && !is_null($statData2))
        {

            // Get difference
            $statData2[0] -= $statData1[0];
            $statData2[1] -= $statData1[1];
            $statData2[2] -= $statData1[2];
            $statData2[3] -= $statData1[3];

            // Sum up the 4 values for User, Nice, System and Idle and calculate
            // the percentage of idle time (which is part of the 4 values!)
            $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];

            // Invert percentage to get CPU time, not idle time
            $load = 100 - ($statData2[3] * 100 / $cpuTime);
        }

        return $load;
    }

    public function adbGetCores() : array
    {
        $this->adbConnect();

        $numberOfCores = shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell cat /sys/devices/system/cpu/present');
        $numberOfCores = current(explode(PHP_EOL, $numberOfCores));
        $numberOfCores = explode('-', $numberOfCores);
        $numberOfCores = intval(end($numberOfCores)) + 1;

        $cpuFrequencyCommand = 'adb -s ' . escapeshellcmd($this->ip) . ' shell cat /sys/devices/system/cpu/cpu%1d/cpufreq/%2s';
        $result = [];

        for ($i = 0; $i < $numberOfCores; $i++)
        {
            $result['Core ' . $i] = [
                'current' => shell_exec(sprintf($cpuFrequencyCommand, $i, 'scaling_cur_freq')),
                'minimum' => shell_exec(sprintf($cpuFrequencyCommand, $i, 'cpuinfo_min_freq')),
                'maximum' => shell_exec(sprintf($cpuFrequencyCommand, $i, 'cpuinfo_max_freq')),
            ];
        }

        return $result;
    }

    public function adbGetSystemInformation()
    {
        $this->adbConnect();

        $props = [
            'ro.board.platform'         => 'Platform',
            'ro.build.version.sdk'      => 'Android SDK',
            'ro.build.version.release'  => 'Android version',
            'ro.product.brand'          => 'Brand',
            'vendor.display-size'       => 'Resolution',
            'ro.product.model'          => 'Model',
        ];
        $command = 'adb -s ' . escapeshellcmd($this->ip) . ' shell getprop | grep "';
        $index = 0;

        foreach($props as $prop => $name)
        {
            $command .= '\[' . $prop . '\]';
            if ($index++ < count($props) - 1)
            {
                $command .= '\|';
            }
        }

        $output = shell_exec($command . '"');
        $output = explode(PHP_EOL, trim($output));
        $result = [];

        foreach($output as $line)
        {
            $line = str_replace(['[', ']'], '', $line);
            list($prop, $value) = explode(':', $line);
            $name = $props[$prop];
            $result[$name] = trim($value);
        }
        
        return $result;
    }
}
