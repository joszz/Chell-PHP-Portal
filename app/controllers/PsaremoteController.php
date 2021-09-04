<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Psaremote;

/**
 * @package Controllers
 */
class PsaremoteController extends WidgetController
{
    private Psaremote $_model;

    public function initialize()
    {
		parent::initialize();

        $this->_model = new Psaremote();
        $this->view->disable();
    }

    public function indexAction()
    {
        $this->response->setJsonContent($this->_model->GetVehicleInfo())->send();
    }
}