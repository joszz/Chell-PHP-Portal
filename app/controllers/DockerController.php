<?php

namespace Chell\Controllers;

use Chell\Models\Docker;

class DockerController extends WidgetController
{
    private Docker $_model;

    /**
     * Initializes the controller, creating a new Disk model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Docker();
    }

    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getContainers())->send();
    }
}
