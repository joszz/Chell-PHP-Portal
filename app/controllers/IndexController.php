<?php
/**
 * The controller responsible for all dashboard related actions.
 * 
 * @package Chell\Controllers
 */
class IndexController extends BaseController
{
    /**
     * Shows the dashboard view
     * 
     * @return  The dashboard view
     */
    public function indexAction()
    {
        $this->view->disks = Diskdrives::DiskStatisticsLocal();
        $this->view->devices = Devices::find(array('order' => 'name ASC'));
    }

    /**
     * Called through AJAX to retrieve settings from config.ini [dashboard]
     * 
     * @return  A JSON encoded object containing variables from [dashboard]
     */
    public function dashboardSettingsAction()
    {
        die(json_encode($this->config->dashboard));
    }
}