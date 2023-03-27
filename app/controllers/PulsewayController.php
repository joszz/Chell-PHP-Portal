<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Pulseway;

/**
 * The controller responsible for all Pulseway related actions.
 *
 * @package Controllers
 */
class PulsewayController extends WidgetController
{
    private Pulseway $_model;

    /**
     * Initializes the controller, creating a new Pulseway model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Pulseway();
        $this->view->disable();
    }

    /**
     * Contacts the Pulseway API for each specified system in the config.
     */
    public function indexAction()
    {
        $systems = explode(',', $this->settings->pulseway->systems->value);
        $result = [];

        foreach ($systems as $system)
        {
            $result[] = $this->_model->getSystem($system);
        }

        $this->response->setJsonContent($result)->send();
    }

    /**
     * Retrieves all systems defined in the associated Pulseway account.
     */
    public function systemsAction()
    {
        $this->settings->pulseway->username->value = $_POST['pulseway-username'];
        $this->settings->pulseway->password->value = $_POST['pulseway-password'];
        $this->settings->pulseway->url->value = $_POST['pulseway-url'];
        $this->response->setJsonContent($this->_model->getSystems())->send();
    }
}