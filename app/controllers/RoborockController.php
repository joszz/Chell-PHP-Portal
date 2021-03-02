<?php

namespace Chell\Controllers;

use Chell\Models\Roborock;

/**
 * The controller responsible for all Roborock related actions.
 *
 * @package Controllers
 */
class RoborockController extends BaseController
{
    /**
     * Called by AJAX to refresh the dashboard widget.
     * Returns a JSON encoded string and dies.
     */
    public function indexAction()
    {
        die(json_encode(Roborock::GetStatus($this->config)));
    }

    /**
     * Displays the info stats for the configured Roborock.
     */
    public function infoAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->info = Roborock::GetInfo($this->config);
    }

    /**
     * Starts Roborock's cleaning.
     */
    public function startAction()
    {
        die(Roborock::Start($this->config));
    }

    /**
     * Stops Roborock's cleaning.
     */
    public function stopAction()
    {
        die(Roborock::Stop($this->config));
    }
}