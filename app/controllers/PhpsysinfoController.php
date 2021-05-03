<?php

namespace Chell\Controllers;

use Chell\Models\PHPSysInfo;

/**
 * The controller responsible for showing about information of this project.
 *
 * @package Controllers
 */
class PhpsysinfoController extends BaseController
{
    /**
     * Shows version information and has link to code documentation.
     */
    public function indexAction($plugin = 'complete')
    {
        die((new PHPSysInfo())->getData($plugin));
    }
}