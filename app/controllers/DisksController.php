<?php

namespace Chell\Controllers;

use Chell\Models\Disks;

class DisksController extends WidgetController
{
    private Disks $_model;

    /**
     * Initializes the controller, creating a new Disk model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Disks();
    }

    /**
     * Retrieves statistics for all disks
     */
    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getStats())->send();
    }
}
