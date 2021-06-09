<?php

namespace Chell\Controllers;

use Chell\Models\Jellyfin;

class JellyfinController extends BaseController
{
    /**
     * Retrieves all Jellyfin libraries.
     */
    public function viewsAction()
    {
        $this->view->disable();
        $this->response->setJsonContent(array_flip((new Jellyfin())->getViews()))->send();
    }
}
