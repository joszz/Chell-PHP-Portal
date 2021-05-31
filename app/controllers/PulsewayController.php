<?php

namespace Chell\Controllers;

use Chell\Models\Pulseway;

/**
 * The controller responsible for all Pulseway related actions.
 *
 * @package Controllers
 */
class PulsewayController extends BaseController
{
    private $_model;

    /**
     * Initializes the controller, creating a new Pulseway model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Pulseway();
    }

    /**
     * Contacts the Pulseway API for each specified system in the config.
     */
    public function indexAction()
    {
        $systems = explode(',', $this->settings->pulseway->systems);
        $result = [];

        foreach ($systems as $system)
        {
            $result[] = $this->_model->getSystem($system);
        }

        die(json_encode($result));
    }

    /**
     * Retrieves all systems defined in the associated Pulseway account.
     */
    public function systemsAction()
    {
        die(json_encode($this->_model->getSystems()));
    }
}