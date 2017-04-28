<?php

namespace Chell\Controllers;

use Chell\Models\Devices;
use Chell\Models\Kodi\KodiMovies;
use Chell\Models\Kodi\KodiMusic;
use Chell\Models\Kodi\KodiTVShowEpisodes;
use Chell\Models\PHPSysInfo;

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
        $this->view->devices = Devices::find(array('order' => 'name ASC'));
        $this->view->movies = KodiMovies::getLatestMovies();
        $this->view->albums = KodiMusic::getLatestAlbums();
        $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();

        $this->executionTime = -microtime(true);
        $this->view->phpsysinfoData = PHPSysInfo::getData($this->config);
        $this->view->PHPSysinfoExecutionTime = round(($this->executionTime + microtime(true)), 2) . 's';
    }
}