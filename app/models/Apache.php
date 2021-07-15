<?php

namespace Chell\Models;

class Apache extends BaseModel
{
    public function getServerStatus()
    {
        $curl = curl_init($this->_settings->apache->server_status_url . '?auto');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        curl_close($curl);

        $data = explode(PHP_EOL, $data);
        $result = [];

        foreach($data as $line)
        {
            if (strrpos($line, ':') !== false)
            {
                list($key, $value) = explode(':', $line, 2);
                $result[trim($key)] = trim($value);
            }
        }

        return $result;
    }

    public function getFpmStatus()
    {
        $curl = curl_init($this->_settings->apache->fpm_status_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        curl_close($curl);

        $data = explode(PHP_EOL, $data);
        $result = [];

        foreach($data as $line)
        {
            if (strrpos($line, ':') !== false)
            {
                list($key, $value) = explode(':', $line, 2);
                $result[trim($key)] = trim($value);
            }
        }

        return $result;
    }
}