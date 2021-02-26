<?php

namespace Chell\Controllers;

use Chell\Models\Roborock;

/**
 * The controller responsible for showing all Verisure related actions.
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

    public function infoAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->info = Roborock::GetInfo($this->config);
    }

    public function startAction()
    {
        die(Roborock::Start($this->config));
    }

    public function stopAction()
    {
        die(Roborock::Stop($this->config));
    }
}