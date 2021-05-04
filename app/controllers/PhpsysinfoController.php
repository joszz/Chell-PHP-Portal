<?php

namespace Chell\Controllers;

use Chell\Models\PHPSysInfo;

/**
 * The controller responsible for all PHPSysInfo related actions.
 *
 * @package Controllers
 */
class PhpsysinfoController extends BaseController
{
    /**
     * Calls the PHPSysInfo API, with specified plugins.
     * 
     * @param string $plugin    Which PHPSysInfo plugin details to retrieve. Defaults to all/complete.
     */
    public function indexAction($plugin = 'complete')
    {
        die((new PHPSysInfo())->getData($plugin));
    }
}