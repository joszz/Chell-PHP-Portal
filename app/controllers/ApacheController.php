<?php

namespace Chell\Controllers;

use Chell\Models\Apache;
use Chell\Controllers\WidgetController;

class ApacheController extends WidgetController
{
    private Apache $_model;

    /**
     * Initializes the controller, creating a new Apache model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Apache();
    }

    /**
     * Used by the widget to update through AJAX.
     */
    public function indexAction()
    {
        $this->view->disable();
        $this->response->setJsonContent($this->_model->getServerStatus())->send();
    }

    /**
     * Details page showing
     */
    public function detailsAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->apache = $this->_model->getServerStatus();
        
        if (!empty($this->_settings->apache->fpm_status_url))
        {
            $this->view->php = $this->_model->getFpmStatus();
        }
    }
}