<?php

namespace Chell\Controllers;

use Chell\Models\Apache;
use Chell\Controllers\WidgetController;

class ApacheController extends WidgetController
{
    private Apache $_model;

    /**
     * Initializes the controller, creating a new Opcache model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Apache();
    }

    public function indexAction()
    {
        $this->view->disable();
        $this->response->setJsonContent($this->_model->getServerStatus())->send();
    }

    public function detailsAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->apache = $this->_model->getServerStatus();
        $this->view->php = $this->_model->getFpmStatus();
    }
}