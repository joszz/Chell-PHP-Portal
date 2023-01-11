<?php

namespace Chell\Controllers;

use Chell\Models\AdGuard;

/**
 * The controller responsible for all AdGuard widget related actions.
 *
 * @package Controllers
 */
class AdGuardController extends WidgetController
{
    private AdGuard $_model;

    /**
     * Initializes the controller, creating a new AdGuard model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new AdGuard();
    }

    /**
     * Get the AdGuard statistics as JSON.
     */
    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getStats())->send();
    }
}