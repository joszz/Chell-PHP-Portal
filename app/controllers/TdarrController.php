<?php

namespace Chell\Controllers;

use Chell\Models\Tdarr;
use Chell\Models\Widget;

/**
 * The controller responsible for Disk related actions.
 *
 * @package Controllers
 */
class TdarrController extends WidgetController
{
    private Tdarr $_model;

    /**
     * Initializes the controller, creating a new Disk model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Tdarr();
    }

    /**
     * Sets the Bootstrap panel size for the widget.
     */
    public function setPanelSize()
    {
        $this->widget = new Widget(12, 4);
    }

    /**
     * Retrieves statistics for all disks.
     */
    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getStats())->send();
    }
}
