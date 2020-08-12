<?php

namespace Chell\Controllers;

use Chell\Models\Verisure;

/**
 * The controller responsible for showing about information of this project.
 *
 * @package Controllers
 */
class VerisureController extends BaseController
{


    public function indexAction()
    {
        die(Verisure::GetOverview($this->config, false));
    }

    public function detailsAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->overflow = true;
        $this->view->overview = Verisure::GetOverview($this->config, true);
        $this->view->log = Verisure::GetLog($this->config);
        //die(var_dump($this->view->log));
    }
}