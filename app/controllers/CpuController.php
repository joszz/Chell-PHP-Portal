<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Widget;
use Chell\Models\Cpu;

/**
 * The controller responsible for all CPU widget related actions.
 *
 * @package Controllers
 */
class CpuController extends WidgetController
{
    private Cpu $_model;

    /**
     * Initializes the controller, creating a new Cpu model.
     */
    public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Cpu();
    }

    /**
     * Sets the Bootstrap panel size for the widget.
     */
    public function setPanelSize()
    {
        $this->widget = new Widget(12, 8);
    }

    /**
     * Adds the assets for the widget.
     */
    public function addAssets()
    {
        $this->jsFiles = ['chart', 'luxon', 'chartjs-adapter-luxon', 'chartjs-plugin-streaming'];
    }

    /**
     * Retrieves the current CPU usage.
     */
    public function indexAction()
    {
        $this->response->setJsonContent($this->_model->getCurrentCpuUsage())->send();
    }
}