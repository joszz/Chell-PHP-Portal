<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 */
class Devices extends Model
{
    public $id;
    public $name;
    public $ip;
    public $mac;
    public $webtemp;
    public $shutdown_method;
    public $show_on_dashboard;

    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->hasMany(
            'id',
            'MenuItems',
            'device_id'
        );
    }

    /**
     * Calls pingExec with IP and returns the state of the device.
     *
     * @param string $ip    Which device to ping
     * @return bool         Whether the device is on (true) or off (false)
     */
    public static function isDeviceOn($ip)
    {
        return self::pingExec($ip) !== false;
    }

    /**
     * Pings a device and returns the response time.
     *
     * @param string        $host   Which host to ping
     * @param int           $ttl    The TimeToLive for the ping request. Defaults to 1 second
     * @return bool|double          The time it took for the device to respond or false if failed.
     */
    private static function pingExec($host, $ttl = 10)
    {
        $latency = false;
        $ttl     = escapeshellcmd($ttl);
        $host    = escapeshellcmd($host);

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
     * Wakes up a device by MAC address.
     *
     * @param string    $mac            The device to wake.
     * @param int       $socket_number  The port to send the magic packet to.
     * @param int       $repetition     The amount of repetition of the MAC in the magic packet. Defaults to 16.
     * @return bool                     Whether or not socket_sendto with magic packet succeeded.
     */
    public static function wakeOnLan($mac, $config, $socket_number = 7, $repetition = 16)
    {
        $addr_byte = explode(':', $mac);
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

            if (socket_sendto($s, $msg, strlen($msg), 0, $config->network->broadcast, $socket_number))
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
     * Executes a RPC command to shutdown Windows based devices.
     *
     * @see                     https://www.howtogeek.com/howto/windows-vista/enable-mapping-to-hostnamec-share-on-windows-vista/
     * @param string $ip        The device to shutdown
     * @param string $user      A valid Windows account to authenticate with.
     * @param string $password  A valid Windows password to authenticate with.
     * @return array            The output of the RPC command on the shell.
     */
    public static function shutdown($ip, $user, $password)
    {
        $ip = escapeshellcmd($ip);
        $user = trim(escapeshellcmd($user));
        $password = trim(escapeshellcmd($password));
        $output = [];

        exec('net rpc shutdown -I ' . $ip . ' -U ' . $user . '%' . $password, $output);

        return $output;
    }
}
