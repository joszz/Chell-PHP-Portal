<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\PHPSysInfo;

/**
 * The controller responsible for all PHPSysInfo related actions.
 *
 * @package Controllers
 */
class PhpsysinfoController extends WidgetController
{
    /**
     * Calls the PHPSysInfo API, with specified plugins.
     *
     * @param string $plugin    Which PHPSysInfo plugin details to retrieve. Defaults to all/complete.
     */
    public function indexAction(string $plugin = 'complete')
    {
        $this->view->disable();
        $this->response->setContentType('application/json');
        $this->response->setContent((new PHPSysInfo())->getData($plugin))->send();
    }
}