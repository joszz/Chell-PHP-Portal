<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Youless;

/**
 * The controller responsible for all YouLess related actions.
 *
 * @package Controllers
 */
class YoulessController extends WidgetController
{
    private Youless $_model;

    /**
     * Initializes the controller, creating a new Roborock model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Youless();
    }

    /**
     * Returns a json array with both the current power usage and the class associated with the value.
     */
    public function indexAction()
    {
        $stats = $this->_model->getCurrentStats();

        $this->view->disable();
        $this->response->setJsonContent([
            'power' => $stats->pwr,
            'counter' => $stats->cnt,
            'class' => $this->_model->getTextClass($stats->pwr)
        ])->send();
    }
}