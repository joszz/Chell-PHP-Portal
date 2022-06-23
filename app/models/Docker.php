<?php

namespace Chell\Models;

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
        $output = shell_exec('sudo docker ps --format \'{"image": "{{ .Image }}", "name":"{{ .Names }}", "status":"{{ .Status }}", "ports":"{{ .Ports }}"},\'');
        $output = json_decode('[' . substr($output, 0, -2) . ']');
        usort($output, fn($a, $b) => strcmp($a->name, $b->name));
        return $output;
    }
}
