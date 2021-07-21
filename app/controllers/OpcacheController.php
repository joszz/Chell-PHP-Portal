<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Opcache;

/**
 * The controller responsible for all Opcache related actions.
 *
 * @package Controllers
 */
class OpcacheController extends WidgetController
{
    protected array $jsFiles = ['chartist', 'chartist-plugin-legend'];

    private Opcache $_model;

    /**
     * Initializes the controller, creating a new Opcache model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Opcache();
    }

    /**
     * Called through AJAX to retrieve the opache data.
     */
    public function datasetAction()
    {
        $this->view->disable();
        $this->response->setContentType('application/json');
        $this->response->setContent($this->_model->getGraphDataSetJson())->send();
    }

    /**
     * Called by fancybox as iFrame when clicking the stats button.
     *
     * @param string $tab               The active tab to display, defaults to 'status'. Will be set to 'scripts' when paging the scripts tab.
     * @param int    $currentPage       The page to display, defaults to 1
     */
    public function detailsAction(string $tab = 'status', int $currentPage = 1)
    {
        $totalPages = 0;
        $scripts = $this->_model->getScriptStatusRows($currentPage, $totalPages, $this->settings->application->items_per_page);

        $this->assets->styles[] = 'opcache';
        $this->view->scripts = $scripts;
        $this->view->paginator = $this->GetPaginator($currentPage, $totalPages, 'opcache/details/scripts/');
        $this->view->opcache = $this->_model;
        $this->view->activeTab = $tab;
        $this->view->overflow = true;
        $this->SetEmptyLayout();
    }
}