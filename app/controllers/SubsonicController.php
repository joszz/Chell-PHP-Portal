<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;

/**
 * The controller responsible for all Subsonic widget related actions.
 *
 * @package Controllers
 */
class SubsonicController extends WidgetController
{
    /**
     * Adds the assets for the widget.
     */
    public function addAssets()
    {
        $this->jsFiles = ['spark-md5'];
    }
}