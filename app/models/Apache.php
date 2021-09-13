<?php

namespace Chell\Models;

use GuzzleHttp\Client;

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
        $client = new Client(['base_uri' => $this->_settings->apache->server_status_url . '?auto']);
        $data = $client->request('GET')->getBody();
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
        $client = new Client();
        $data = $client->request('GET', $this->_settings->apache->fpm_status_url)->getBody();
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