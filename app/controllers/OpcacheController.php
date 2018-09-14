<?php

namespace Chell\Controllers;

use Chell\Models\Opcache;

/**
 * The controller responsible for all Opcache related actions.
 *
 * @package Controllers
 */
class OpcacheController extends BaseController
{
    /**
     * Called through AJAX to retrieve the opache data.
     *
     * @return string   A JSON encoded array.
     */
    public function datasetAction()
    {
        $opcache = new Opcache();
        die($opcache->getGraphDataSetJson());
    }

    /**
     * Called by fancybox as iFrame when clicking the stats button.
     */
    public function detailsAction($page = 1)
    {
        $opcache = new Opcache();

        $totalPages = 0;
        $scripts = $opcache->getScriptStatusRows($page, $totalPages);
        
        $page = new \stdClass();
        $page->total_pages = $totalPages;
        $page = $this->SetPaginatorEndAndStart($page);

        $this->view->scripts = $scripts;
        $this->view->scriptsPaginator = $page;
        $this->view->opcache = $opcache;
        $this->view->setMainView('layouts/empty');


    }
}