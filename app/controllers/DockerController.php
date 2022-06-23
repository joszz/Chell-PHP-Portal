<?php

namespace Chell\Controllers;

use Chell\Models\Docker;

/**
 * The controller responsible for Docker related actions.
 *
 * @package Controllers
 */
class DockerController extends WidgetController
{
    private Docker $_model;

    /**
     * Initializes the controller, creating a new Docker model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Docker();
    }

    /**
     * Retrieves an array of objects representing the Docker containers.
     */
    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getContainers())->send();
    }
}
