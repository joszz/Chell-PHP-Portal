<?php
/**
 * The controller responsible for all dashboard related actions.
 * 
 * @package Chell\Controllers
 */
class IndexController extends BaseController
{
    /**
     * Shows the dashboard view
     * 
     * @return  The dashboard view
     */
    public function indexAction()
    {
        $this->view->menu = Menus::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => 1),
        ));

        $this->view->disks = Diskdrives::DiskStatisticsLocal();
        $this->view->devices = Devices::find(array('order' => 'name ASC'));
        $this->view->movies = KodiMovies::getLatestMovies();
        $this->view->albums = KodiMusic::getLatestAlbums();
        $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
    }

    /**
     * Called through AJAX to retrieve settings from config.ini [dashboard]
     * 
     * @return  A JSON encoded object containing variables from [dashboard]
     */
    public function dashboardSettingsAction()
    {
        die(json_encode($this->config->dashboard));
    }
}