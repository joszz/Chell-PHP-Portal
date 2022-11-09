<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Sonos;

/**
 * The controller responsible for all Verisure related actions.
 *
 * @package Controllers
 */
class SonosController extends WidgetController
{
    private Sonos $_model;

    /**
     * Initializes the controller, creating a new Verisure model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Sonos();
    }

    /**
     * Called by AJAX to refresh the dashboard widget.
     * Returns a JSON encoded string and dies.
     */
    public function indexAction()
    {
        $this->view->disable();
        $this->_model->getPlaybackStatus();
    }
}