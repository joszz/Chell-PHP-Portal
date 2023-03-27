<?php

namespace Chell\Models;

use Exception;
use stdClass;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to Tdarr.
 *
 * @package Models
 */
class Tdarr extends BaseModel
{
    /**
     * Retrieves statistics for Tdarr
     *
     * @return array|bool   An array of statistics or false if failed.
     */
    public function getStats()
    {
        try
        {
            $response = $this->getHttpClient('cruddb');
        }
        catch (Exception $exception)
        {
            $this->logger->LogException($exception);
            return false;
        }

        $output = $response->getBody();

        if (!empty($output))
        {
            $output = json_decode($output);

            $result = [];

            foreach ($output->pies[0][6] as $statistic)
            {
                $result[$statistic->name] = $statistic->value;
            }

            return $result;
        }

        return false;
    }

    /**
     * Gets the ResponseInterface to be used to invoke the Tdarr API.
     *
     * @param string $url           The Tdarr endpoint to call.
     * @return ResponseInterface    The ResponseInterface to call the API with.
     */
    private function getHttpClient(string $url) : ResponseInterface
    {
        $client = new Client();
        return $client->post($this->settings->tdarr->url->value . $url, [
            RequestOptions::JSON => [
                'data' => [
                    'collection' => 'StatisticsJSONDB',
                    'mode' => 'getById',
                    'docID' => 'statistics',
                    'obj' => new stdClass()
                ],
                'headers' => [
                    'content-Type' => 'application/json'
                ],
                'timeout' => 1000
            ]
        ]);
    }
}
