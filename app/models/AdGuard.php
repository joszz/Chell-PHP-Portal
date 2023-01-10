<?php

namespace Chell\Models;

use Exception;
use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to Sonos.
 *
 * @package Models
 */
class AdGuard extends BaseModel
{

    public function getStats(){
        $result = $this->getHttpClient('https://adguard.gotgeeks.nl/control/stats', 'GET');
        return ['DNS Queries' => $result->num_dns_queries, 'Blocked by Filters ' => $result->num_blocked_filtering  ];
    }

    private function getBasicAuthorization()
    {
        return 'Basic ' . base64_encode($this->settings->adguard->username . ':' . $this->settings->adguard->password);
    }

    private function getHttpClient(string $url, string $method, array $options = [])
    {
        $client = new Client(['headers' => ['Authorization' => $this->getBasicAuthorization()]]);

        try
        {
            $response = $client->request($method, $url, $options);
            return json_decode($response->getBody()->getContents());
        }
        catch (Exception $exception)
        {
            $this->logger->LogException($exception);
            return false;
        }
    }
}