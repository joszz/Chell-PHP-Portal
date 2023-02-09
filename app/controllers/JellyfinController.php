<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Jellyfin;
use Chell\Models\Widget;

/**
 * The controller responsible for all Jellyfin widget related actions.
 *
 * @package Controllers
 */
class JellyfinController extends WidgetController
{
    private Jellyfin $_model;

    /**
     * Initializes the controller, creating a new Motion model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Jellyfin();
        $this->view->disable();
    }

    /**
     * Adds the assets for the widget.
     */
    public function addAssets()
    {
        $this->jsFiles[] = 'gallery';
        $this->cssFiles[] = 'gallery';
    }

    /**
     * Sets the Bootstrap panel size for the widget.
     */
    public function setPanelSize()
    {
        $this->widget = new Widget(12, 6, 4, true);
    }

    public function indexAction()
    {
        $this->response->setJsonContent($this->_model->getLatestForViews())->send();
    }

    /**
     * Retrieves all Jellyfin libraries.
     */
    public function viewsAction()
    {
        $this->view->disable();
        $this->settings->jellyfin->userid = $_POST['jellyfin-userid'];
        $this->settings->jellyfin->token = $_POST['jellyfin-token'];
        $this->settings->jellyfin->url = $_POST['jellyfin-url'];

        $this->response->setJsonContent($this->_model->getViews())->send();
    }
}
