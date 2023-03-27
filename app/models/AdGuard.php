<?php

namespace Chell\Models;

use Exception;
use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to AdGuard.
 *
 * @package Models
 */
class AdGuard extends BaseModel
{

    /**
     * Retrieves DNS stats from the AdGuard API
     *
     * @return array
     */
    public function getStats() : array
    {
        $result = $this->getHttpClient($this->settings->adguard->url->value . '/control/stats', 'GET');
        return ['DNS Queries' => $result->num_dns_queries, 'Blocked by Filters ' => $result->num_blocked_filtering];
    }

    /**
     * Gets a Basic auth string to use as a Authorization header.
     * @return string
     */
    private function getBasicAuthorization() : string
    {
        return 'Basic ' . base64_encode($this->settings->adguard->username->value . ':' . $this->settings->adguard->password->value);
    }

    /**
     * Calls the AdGuard API with specified $url and $method.
     *
     * @param string $url           The AdGuard API URL to call.
     * @param string $method        The method to call the AdGuard API with, either 'POST' or 'GET'
     * @return bool|\stdclass       Either a boolean indicating failure or a stdclass with a deserialized JSON object.
     */
    private function getHttpClient(string $url, string $method)
    {
        $client = new Client(['headers' => ['Authorization' => $this->getBasicAuthorization()]]);

        try
        {
            $response = $client->request($method, $url);
            return json_decode($response->getBody()->getContents());
        }
        catch (Exception $exception)
        {
            $this->logger->LogException($exception);
            return false;
        }
    }
}