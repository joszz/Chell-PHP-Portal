<?php

namespace Chell\Controllers;

use Chell\Models\Youless;

/**
 *
 * @package Controllers
 */
class YoulessController extends BaseController
{
    public function indexAction()
    {
        $power = (new Youless())->getCurrentPowerUsage($this->config);
        die(json_encode(array("power" => $power, "class" => Youless::getTextClass($this->config, $power))));
    }
}