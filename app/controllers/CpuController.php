<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Widget;
use Chell\Models\Cpu;

/**
 * The controller responsible for all CPU widget related actions.
 * Currently just used as a stub, to indicate this widget exists.
 *
 * @package Controllers
 */
class CpuController extends WidgetController
{
    private Cpu $_model;

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

    public function addAssets()
    {
        $this->jsFiles = ['chart', 'luxon', 'chartjs-adapter-luxon', 'chartjs-plugin-streaming'];
    }

    public function indexAction()
    {
        $this->response->setJsonContent($this->_model->getCurrentCpuUsage())->send();
    }
}