<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\DatabaseStats;

/**
 * The controller responsible for all database statistic related actions.
 *
 * @package Controllers
 */
class DatabaseStatsController extends WidgetController
{
    /**
     * Sends the database stats as JSON to be consumed by AJAX in the frontend.
     */
    public function indexAction()
    {
        $this->view->disable();

        $stats = (new DatabaseStats())->getStats();
        $this->response->setJsonContent($stats)->send();
    }
}