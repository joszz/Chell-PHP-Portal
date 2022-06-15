<?php

namespace Chell\Controllers;

use Chell\Models\Disk;
use Chell\Models\Widget;

class DiskController extends BaseController
{
    private Disk $_model;

    /**
     * Initializes the controller, creating a new Disk model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Disk();
    }

    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getSpindownStatsForMountpoint($_GET['mountpoint']))->send();
    }
}
