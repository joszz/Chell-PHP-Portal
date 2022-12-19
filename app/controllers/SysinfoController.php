<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Sysinfo;
use Chell\Models\Widget;

/**
 * The controller responsible for all Sysinfo related actions.
 *
 * @package Controllers
 */
class SysinfoController extends WidgetController
{
    /**
     * Sets the Bootstrap panel size for the widget.
     */
    public function setPanelSize()
    {
        $this->widget = new Widget(12, 4);
    }

    /**
     * Retrieves the system information as JSON.
     */
    public function indexAction() 
    {
        $this->view->disable();
        $this->response->setJsonContent((new Sysinfo())->getData())->send();
    }
}