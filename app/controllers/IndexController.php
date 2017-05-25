<?php

namespace Chell\Controllers;

use Phalcon\Mvc\View;

use Chell\Models\Couchpotato;
use Chell\Models\Devices;
use Chell\Models\PHPSysInfo;
use Chell\Models\Kodi\KodiMovies;
use Chell\Models\Kodi\KodiMusic;
use Chell\Models\Kodi\KodiTVShowEpisodes;

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
        $this->view->couchpotato = Couchpotato::getAllMovies($this->config);

        $this->executionTime = -microtime(true);
        $this->view->phpsysinfoData = PHPSysInfo::getData($this->config);
        $this->view->PHPSysinfoExecutionTime = round(($this->executionTime + microtime(true)), 2) . 's';
    }

    /**
     * Renders the manifest.json file, used for favicons and the like.
     */
    public function manifestAction()
    {
        header('Content-Type: application/manifest+json; charset=UTF-8');
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }
}