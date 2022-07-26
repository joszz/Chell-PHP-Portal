<?php

namespace Chell\Controllers;

use Chell\Models\Sonarr;
use Chell\Models\Widget;

/**
 * The controller responsible for Disk related actions.
 *
 * @package Controllers
 */
class SonarrController extends WidgetController
{
    private Sonarr $_model;

    /**
     * Initializes the controller, creating a new Disk model.
     *
     * @todo evo calender for opkomende series?
     * https://edlynvillegas.github.io/evo-calendar/
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Sonarr();
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
        $this->widget = new Widget(12, 4);
    }

    /**
     * Retrieves statistics for all disks.
     */
    public function indexAction()
    {
        return $this->response->setJsonContent($this->_model->getCalender($_GET['start'], $_GET['end']))->send();
    }
}
