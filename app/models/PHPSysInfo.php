<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class PHPSysInfo extends Model
{
    /**
     * Main function retrieving PHPSysInfo JSON through cURL.
     *
     * @param object $config    The configuration file to use.
     * @return bool|string      All PHPSysInfo data in an associative array
     */
    public static function getData($config, $plugin)
    {
        $curl = curl_init($config->phpsysinfo->URL . 'xml.php?json&plugin=' . $plugin . '&t=' . time());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, $config->phpsysinfo->username . ':' . $config->phpsysinfo->password);
        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }
}