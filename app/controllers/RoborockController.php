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
        $this->view->setMainView('layouts/empty');
        $this->view->info = $this->_model->getInfo();
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