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
        $stats = (new Youless())->getCurrentStats($this->config);
        die(json_encode(['power' => $stats->pwr, 'counter' => $stats->cnt, 'class' => Youless::getTextClass($this->config, $stats->pwr)]));
    }
}