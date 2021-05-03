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
     * @param object $config    The configuration file to use.
     * @return bool|string      All PHPSysInfo data in an associative array
     */
    public function getData($plugin)
    {
        $curl = curl_init($this->_config->phpsysinfo->URL . 'xml.php?json&plugin=' . $plugin . '&t=' . time());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_config->phpsysinfo->username . ':' . $this->_config->phpsysinfo->password);
        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }
}