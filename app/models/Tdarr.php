<?php

namespace Chell\Models;

use Chell\Exceptions\ChellException;
use Exception;
use stdClass;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Tdarr extends BaseModel
{
    public function getStats()
    {
        try
        {
            $response = $this->getHttpClient('cruddb');
        }
        catch (Exception $e)
        {
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
    }

    private function getHttpClient(string $url) : ResponseInterface
    {
        $client = new Client();
        return $client->post($this->_settings->tdarr->url . $url, [
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
