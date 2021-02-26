<?php

namespace Chell\Controllers;

use Chell\Models\Jellyfin;

/**
 * The controller responsible for showing about information of this project.
 *
 * @package Controllers
 */
class JellyfinController extends BaseController
{
    /**
     * Shows version information and has link to code documentation.
     */
    public function indexAction()
    {
        $views = Jellyfin::GetViews($this->config);

        foreach($views as $view => $viewId)
        {
            $this->view->{$view} = Jellyfin::GetLatest($this->config, $viewId);
        }
    }
}