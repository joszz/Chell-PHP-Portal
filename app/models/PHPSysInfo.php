<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class PHPSysInfo extends BaseModel
{
    /**
     * Main function retrieving PHPSysInfo JSON through cURL.
     *
     * @return bool|string      All PHPSysInfo data in an associative array
     */
    public function getData(string $plugin)
    {
        $curl = curl_init($this->_settings->phpsysinfo->url . 'xml.php?json&plugin=' . $plugin . '&t=' . time());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_settings->phpsysinfo->username . ':' . $this->_settings->phpsysinfo->password);
        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }
}