<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Roborock;

/**
 * The controller responsible for all Roborock related actions.
 *
 * @package Controllers
 */
class RoborockController extends WidgetController
{
    private Roborock $_model;

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
     * Returns a JSON encoded string to the browser.
     */
    public function indexAction()
    {
        $this->view->disable();
        $this->response->setJsonContent($this->_model->getStatus())->send();
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

        $this->assets->addStylesAndScripts(['roborock', 'bootstrap-select', 'jquery.bootstrap-touchspin']);

        $this->SetEmptyLayout();
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
        $this->view->disable();
        $this->response->setJsonContent($this->_model->start())->send();
    }

    /**
     * Stops Roborock's cleaning.
     */
    public function stopAction()
    {
        $this->view->disable();
        $this->response->setJsonContent($this->_model->stop())->send();
    }
}