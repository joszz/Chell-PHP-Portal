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
    public function getContainers()
    {
        $output = shell_exec('sudo docker ps --format \'{"image": "{{ .Image }}", "name":"{{ .Names }}", "status":"{{ .Status }}", "ports":"{{ .Ports }}"},\'');
        $output = json_decode('[' . substr($output, 0, -2) . ']');
        usort($output, fn($a, $b) => strcmp($a->name, $b->name));
        return $output;
    }
}
