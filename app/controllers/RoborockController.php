<?php

namespace Chell\Controllers;

use Chell\Models\Roborock;

/**
 * The controller responsible for all Roborock related actions.
 *
 * @package Controllers
 */
class RoborockController extends BaseController
{
    private $_model;

    /**
     * Initializes the controller, creating a new Roborock model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Roborock();
    }

    /**
     * Called by AJAX to refresh the dashboard widget.
     * Returns a JSON encoded string and dies.
     */
    public function indexAction()
    {
        die(json_encode($this->_model->getStatus()));
    }

    /**
     * Displays the info stats for the configured Roborock.
     */
    public function infoAction()
    {
        if ($this->request->isPost())
        {
            $data = $this->request->getPost();
            $this->_model->setFanSpeed($data['fanspeed']);
            $this->_model->setSoundVolume($data['volume']);
            $this->_model->setWaterflow($data['waterflow']);
        }

        $this->view->setMainView('layouts/empty');
        $this->view->info = $this->_model->getInfo();
        $this->view->fanspeed = $this->_model->getFanSpeed();
        $this->view->volume = $this->_model->getSoundVolume();
        $this->view->waterflow = $this->_model->getWaterflow();
    }

    /**
     * Starts Roborock's cleaning.
     */
    public function startAction()
    {
        die($this->_model->start());
    }

    /**
     * Stops Roborock's cleaning.
     */
    public function stopAction()
    {
        die($this->_model->stop());
    }
}