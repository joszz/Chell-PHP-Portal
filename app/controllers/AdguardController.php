<?php

namespace Chell\Controllers;

use Chell\Models\AdGuard;

/**
 * The controller responsible for showing about information of this project.
 *
 * @package Controllers
 */
class AdGuardController extends WidgetController
{
    private AdGuard $_model;

	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new AdGuard();
    }

    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getStats())->send();
    }
}