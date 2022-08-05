<?php

namespace Chell\Controllers;

use Chell\Models\Arr;
use Chell\Models\Widget;

/**
 * @package Controllers
 */
class ArrController extends WidgetController
{
    private Arr $_model;

	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Arr();
    }

    public function addAssets()
    {
        $this->cssFiles = ['theme-basic'];
        $this->jsFiles = ['bundle'];
    }

    /**
     * Sets the Bootstrap panel size for the widget.
     */
    public function setPanelSize()
    {
        $this->widget = new Widget(12, 6, 4);
    }

    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getCalendar($_GET['start'], $_GET['end']))->send();
    }
}
