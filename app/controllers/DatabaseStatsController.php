<?php

namespace Chell\Controllers;

use Chell\Models\DatabaseStats;

/**
 * The controller responsible for all MySQL related actions.
 *
 * @package Controllers
 */
class DatabaseStatsController extends BaseController
{
    public function indexAction()
    {
        $this->view->disable();

        $stats = (new DatabaseStats())->getStats();
        $this->response->setJsonContent($stats)->send();
    }
}