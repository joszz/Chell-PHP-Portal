<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Sysinfo;
use Chell\Models\Widget;

/**
 * The controller responsible for all PHPSysInfo related actions.
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
     * Calls the PHPSysInfo API, with specified plugins.
     *
     * @param string $plugin    Which PHPSysInfo plugin details to retrieve. Defaults to all/complete.
     */
    public function indexAction(string $plugin = 'complete')
    {
        $this->view->disable();
        $this->response->setJsonContent((new Sysinfo())->getData($plugin))->send();
    }
}