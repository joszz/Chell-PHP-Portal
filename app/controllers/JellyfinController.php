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

    /**
     * Retrieves all Jellyfin libraries.
     */
    public function viewsAction()
    {
        $this->view->disable();
        $this->response->setJsonContent(array_flip((new Jellyfin())->getViews()))->send();
    }
}
