<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to Apache.
 *
 * @package Models
 */
class Apache extends BaseModel
{
    /**
     * Retrieves the Apache mod_status URL, defined in settings, and formats them to an array where the key of the array is the metric.
     *
     * @return string[]     The metrics of apache.
     */
    public function getServerStatus() : array
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

    /**
     * Retrieves the PHP FPM status URL, defined in settings, and formats them to an array where the key of the array is the metric.
     *
     * @return string[]     The metrics of PHP
     */
    public function getFpmStatus() : array
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