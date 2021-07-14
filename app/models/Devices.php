<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
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
            'device_id'
        );

        if ($this->shutdown_method == 'adb')
        {
            $this->adbConnect();
        }
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
     * @return bool|double          The time it took for the device to respond or false if failed.
     */
    private function ping(int $ttl = 10)
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
    private function adbIsAwake() : bool
    {
        $output = strtolower(shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell dumpsys power | grep -e "mWakefulness" | head -1'));
        return strpos($output, 'asleep') === false;
    }

    /**
     * Wakes up a device by MAC address.
     *
     * @param int       $socket_number  The port to send the magic packet to.
     * @param int       $repetition     The amount of repetition of the MAC in the magic packet. Defaults to 16.
     * @return bool                     Whether or not socket_sendto with magic packet succeeded.
     */
    private function wakeOnLan(int $socket_number = 7, int $repetition = 16) : bool
    {
        $addr_byte = explode(':', $this->mac);
        $hw_addr = '';
        $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);

        for ($a=0; $a <6; $a++)
        {
            $hw_addr .= chr(hexdec($addr_byte[$a]));
        }

        for ($a = 1; $a <= $repetition; $a++)
        {
            $msg .= $hw_addr;
        }

        // send it to the broadcast address using UDP
        // SQL_BROADCAST option isn't help!!
        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($s == false)
        {
            echo 'Error creating socket!\n';
            echo 'Error code is "' . socket_last_error($s) . '" - ' . socket_strerror(socket_last_error($s));
            return false;
        }
        else
        {
            // setting a broadcast option to socket:
            $opt_ret = socket_set_option($s, 1, 6, true);

            if ($opt_ret <0)
            {
                echo 'setsockopt() failed, error: ' . $opt_ret . '\n';
                return false;
            }

            if (socket_sendto($s, $msg, strlen($msg), 0, $this->broadcast, $socket_number))
            {
                echo 'Magic Packet sent successfully!';
                socket_close($s);
                return true;
            }
            else
            {
                echo 'Magic packet failed!';
                return false;
            }
        }
    }

    /**
     * Uses ADB to sent a wakeup keyevent to wake the device.
     *
     * @return bool Always returns true since there is no proper way to determine if the call was successful.
     */
    private function wakeOnAdb() : bool
    {
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
        shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell input keyevent KEYCODE_SLEEP');
        return true;
    }

    private function adbConnect()
    {
        shell_exec('adb connect ' . escapeshellcmd($this->ip));
        return true;
    }

    public function adbGetArchitecture()
    {
        return shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell getprop ro.product.cpu.abi');
    }

    public function adbGetBatteryStats()
    {
        return shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell dumpsys battery');
    }

    public function adbGetCpuUsage()
    {
        $this->adbConnect();
        $output = shell_exec('adb -s ' . escapeshellcmd($this->ip) . ' shell cat /proc/stat');
        $result = current(explode(PHP_EOL, $output));
        $result = str_replace('cpu', '', $result);
        $result = array_values(array_filter(explode (' ', $result)));
        $totalTime = 0;

        foreach ($result as $time)
        {
            $totalTime += trim($time);
        }

        return round((1 - $result[3] / $totalTime) * 100);
    }
}
