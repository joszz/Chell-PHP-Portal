<?php

namespace Chell\Controllers;

use Chell\Models\Verisure;

/**
 * The controller responsible for showing all Verisure related actions.
 *
 * @package Controllers
 */
class VerisureController extends BaseController
{
    /**
     * Called by AJAX to refresh the dashboard widget.
     * Returns a JSON encoded string and dies.
     */
    public function indexAction()
    {
        die(Verisure::GetOverview($this->config, true));
    }

    /**
     * Shows all the details of the Verisure installation.
     */
    public function detailsAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->overflow = true;
        $this->view->overview = Verisure::GetOverview($this->config, false);
        $this->view->log = Verisure::GetLog($this->config);
    }
}