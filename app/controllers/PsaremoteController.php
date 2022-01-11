<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Psaremote;

/**
 * The controller responsible for all PSA Remote related actions.
 *
 * @package Controllers
 */
class PsaremoteController extends WidgetController
{
    private Psaremote $_model;

    /**
     * Initializes the controller, creating a new Psaremote model.
     */
    public function initialize()
    {
		parent::initialize();

        $this->_model = new Psaremote();
        $this->view->disable();
    }

    /**
     * Gets the vehicle information as JSON.
     *
     * @param bool $cache   Whether or not to retrieve the information from cache.
     */
    public function indexAction(bool $cache)
    {
        $this->response->setJsonContent($this->_model->GetVehicleInfo($cache))->send();
    }
}