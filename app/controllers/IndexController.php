<?php

class IndexController extends BaseController
{
    public function indexAction()
    {
        
        $this->view->disks = Diskdrives::DiskStatisticsLocal();
        $this->view->devices = Devices::find(array('order' => 'name ASC'));
    }
}