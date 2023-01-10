<?php

namespace Chell\Models;

use stdClass;
use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to docker.
 *
 * @package Models
 */
class Docker extends BaseModel
{
    /**
     * Uses Docker's ps command to retrieve container information as JSON.
     *
     * @return array    An array of objects containing information about all containers.
     */
    public function getContainers() : array
    {
        $client = new Client(['base_uri' => $this->settings->docker->remote_api_url . 'containers/json']);
        $data = json_decode($client->request('GET')->getBody());
        $result = [];

        foreach ($data as $container)
        {
            $formattedContainer = new stdClass();
            $formattedContainer->name = str_replace('/', '', $container->Names[0]);
            $formattedContainer->status = $container->Status;
            $result[] = $formattedContainer;
        }

        usort($result, fn($a, $b) => strcmp($a->name, $b->name));
        return $result;
    }
}
