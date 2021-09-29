<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Widget;

/**
 * The controller responsible for all rCPU widget related actions.
 * Currently just used as a stub, to indicate this widget exists.
 *
 * @package Controllers
 */
class RcpuController extends WidgetController
{
    public function setPanelSize()
    {
        $this->widget = new Widget(12, 8);
    }
}