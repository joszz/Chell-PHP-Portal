<?php

namespace Chell\Controllers;

use Chell\Models\Youless;

/**
 * The controller responsible for all YouLess related actions.
 *
 * @package Controllers
 */
class YoulessController extends BaseController
{
    /**
     * Returns a json array with both the current power usage and the class associated with the value.
     */
    public function indexAction()
    {
        $power = (new Youless())->getCurrentPowerUsage($this->config);
        die(json_encode(array("power" => $power, "class" => Youless::getTextClass($this->config, $power))));
    }
}