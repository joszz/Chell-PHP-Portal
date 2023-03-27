<?php

namespace Chell\Controllers;

use Chell\Models\Apache;
use Chell\Controllers\WidgetController;

/**
 * The controller responsible for all Apache widget related actions.
 *
 * @package Controllers
 */
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
     * Showing details page 
     */
    public function detailsAction()
    {
        $this->SetEmptyLayout();
        $this->view->apache = $this->_model->getServerStatus();

        if (!empty($this->settings->apache->fpm_status_url->value))
        {
            $this->view->php = $this->_model->getFpmStatus();
        }
    }
}