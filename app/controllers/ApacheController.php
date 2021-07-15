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
        $this->view->disable();
    }

    public function indexAction()
    {
        $this->response->setJsonContent($this->_model->getServerStatus())->send();
    }

    public function phpfpmAction()
    {
        $this->response->setJsonContent($this->_model->getFpmStatus())->send();
    }
}