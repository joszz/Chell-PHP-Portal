<?php
/**
 * The controller responsible for all dashboard related actions.
 * 
 * @package Controllers
 */
class IndexController extends BaseController
{
    private $executionTime;

    /**
     * Shows the dashboard view
     */
    public function indexAction()
    {
        $this->view->menu = Menus::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => 1),
        ));

        $this->view->devices = Devices::find(array('order' => 'name ASC'));
        $this->view->movies = KodiMovies::getLatestMovies();
        $this->view->albums = KodiMusic::getLatestAlbums();
        $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
        
        $this->executionTime = -microtime(true);
        $this->view->phpsysinfoData = PHPSysInfo::getData($this->config);
        $this->view->PHPSysinfoExecutionTime = round(($this->executionTime + microtime(true)) * 1000, 2) . '&micro;s';
    }
}