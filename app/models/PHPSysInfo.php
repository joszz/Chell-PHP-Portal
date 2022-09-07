<?php

namespace Chell\Models;

use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 * @suppress PHP2414
 */
class PHPSysInfo extends BaseModel
{
    /**
     * Main function retrieving PHPSysInfo JSON through Guzzle.
     *
     * @return bool|string      All PHPSysInfo data in an associative array
     */
    public function getData(string $plugin)
    {
        $client = new Client();
        $response = $client->request('GET', $this->settings->phpsysinfo->url . 'xml.php?json&plugin=' . $plugin . '&t=' . time(),
			['auth' => [$this->settings->phpsysinfo->username , $this->settings->phpsysinfo->password]]);
        return $response->getBody();
    }
}