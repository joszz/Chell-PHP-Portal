<?php

namespace Chell\Controllers;

use Chell\Models\Arr;
use Chell\Models\Widget;

/**
 * The controller responsible for all Sonarr and Radarr related actions.
 *
 * @package Controllers
 */
class ArrController extends WidgetController
{
    private Arr $_model;

    /**
     * Creates new Arr model and disables the view.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Arr();
    }

    /**
     * Add color-calendar assets/
     */
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

    /**
     * Retrieves the calendar with Sonarr and Radarr entries, defined by a start/end queryparameter for the period to retrieve.
     */
    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getCalendar($_GET['start'], $_GET['end']))->send();
    }
}
