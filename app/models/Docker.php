<?php

namespace Chell\Models;

use stdClass;
use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to docker.
 *
 * @package Models
 * @suppress PHP2414
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
        $client = new Client(['base_uri' => $this->_settings->docker->remote_api_url . 'containers/json']);
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


    //    $output = shell_exec('sudo docker ps --format \'{"image": "{{ .Image }}", "name":"{{ .Names }}", "status":"{{ .Status }}", "ports":"{{ .Ports }}"},\'');
    //    $output = json_decode('[' . substr($output, 0, -2) . ']');
    //    usort($output, fn($a, $b) => strcmp($a->name, $b->name));
    //    return $output;
    //}
}
