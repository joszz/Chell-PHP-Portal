<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Jellyfin;

/**
 * The controller responsible for all Jellyfin widget related actions.
 *
 * @package Controllers
 */
class JellyfinController extends WidgetController
{
    public function addAssets()
    {
        $this->jsFiles[] = 'gallery';
        $this->cssFiles[] = 'gallery';
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
