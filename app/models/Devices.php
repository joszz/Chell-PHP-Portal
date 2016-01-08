<?php

class Devices extends BaseModel
{
    public function initialize()
    {
        $this->hasMany(
            'id',
            'MenuItems',
            'device_id'
        );
    }

    public function isDeviceOn($ip) 
    {
        return self::pingExec($ip) !== false;
    }

    private function pingExec($host, $ttl = 1) 
    {
        $latency = false;
        $ttl     = escapeshellcmd($ttl);
        $host    = escapeshellcmd($host);

        // Exec string for Windows-based systems.
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
        {
            // -n = number of pings; -i = ttl.
            $exec_string = 'ping -n 1 -i ' . $ttl . ' ' . $host;
        }
        // Exec string for UNIX-based systems (Mac, Linux).
        else 
        {
            // -n = numeric output; -c = number of pings; -t = ttl.
            $exec_string = 'ping -n -c 1 -t ' . $ttl . ' ' . $host;
        }

        exec($exec_string, $output, $return);

        // Strip empty lines and reorder the indexes from 0 (to make results more
        // uniform across OS versions).
        $output = array_values(array_filter($output));
        // If the result line in the output is not empty, parse it.
        if (!empty($output[1])) 
        {
            // Search for a 'time' value in the result line.
            $response = preg_match("/time(?:=|<)(?<time>[\.0-9]+)(?:|\s)ms/", $output[1], $matches);
            // If there's a result and it's greater than 0, return the latency.
            if ($response > 0 && isset($matches['time'])) 
            {
                $latency = round($matches['time']);
            }
        }

        return $latency;
    }

    public function wakeOnLan($mac, $socket_number = '7', $repetition = 16) 
    {
        $addr_byte = explode(':', $mac);
        $hw_addr = '';
        for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
        $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
        for ($a = 1; $a <= $repetition; $a++) $msg .= $hw_addr;

        // send it to the broadcast address using UDP
        // SQL_BROADCAST option isn't help!!
        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($s == false) 
        {
            echo 'Error creating socket!\n';
            echo 'Error code is "' . socket_last_error($s) . '" - ' . socket_strerror(socket_last_error($s));
            return FALSE;
        }
        else 
        {
            // setting a broadcast option to socket:
            $opt_ret = socket_set_option($s, 1, 6, TRUE);
            if($opt_ret <0) 
            {
                echo 'setsockopt() failed, error: ' . $opt_ret . '\n';
                return false;
            }
            if(socket_sendto($s, $msg, strlen($msg), 0, $this->config->network->broadcast, $socket_number)) 
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

    public function shutdown($ip, $user, $password)
    {
        $ip = escapeshellcmd($ip);
        $user = trim(escapeshellcmd($user));
        $password = trim(escapeshellcmd($password));
        $output = array();

        exec('net rpc shutdown -I ' . $ip . ' -U ' . $user . '%' . $password, $output);

        return $output;
    }   
}
