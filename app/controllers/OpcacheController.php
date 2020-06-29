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
     *
     * @param int    $currentPage       The page to display, defaults to 1
     * @param string $tab               The active tab to display, defaults to 'status'. Will be set to 'scripts' when paging the scripts tab.
     */
    public function detailsAction($tab = 'status', $currentPage = 1)
    {
        $opcache = new Opcache();
        $totalPages = 0;
        $scripts = $opcache->getScriptStatusRows($currentPage, $totalPages, $this->config->application->itemsPerPage);

        $this->view->scripts = $scripts;
        $this->view->paginator = $this->GetPaginator($currentPage, $totalPages, 'opcache/details/' . $tab .'/');
        $this->view->opcache = $opcache;
        $this->view->activeTab = $tab;
        $this->view->overflow = true;
        $this->view->setMainView('layouts/empty');
    }
}