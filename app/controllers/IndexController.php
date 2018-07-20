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
        $this->view->dnsPrefetchRecords = $this->setDNSPrefetchRecords();

        $this->view->devices = Devices::find(array('order' => 'name ASC'));
        $this->view->movies = KodiMovies::getLatestMovies();
        $this->view->albums = KodiMusic::getLatestAlbums();
        $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
        $this->view->couchpotato = Couchpotato::getAllMovies($this->config);

        $this->executionTime = -microtime(true);
        $this->view->phpsysinfoData = PHPSysInfo::getData($this->config);
        $this->view->phpsysinfoExecutionTime = round(($this->executionTime + microtime(true)), 2) . 's';
    }

    /**
     * Renders the manifest.json file, used for favicons and the like.
     */
    public function manifestAction()
    {
        header('Content-Type: application/manifest+json; charset=UTF-8');
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    /**
     * Sets the URLs for <link rel="dns-prefetch" />
     * 
     * @return array An array with strings for the DNS prefetch URLs
     */
    private function setDNSPrefetchRecords()
    {
        $hostname = getenv('HTTP_HOST');
        $dnsPrefetchRecords = array($this->config->application->tmdbAPIURL);

        foreach($this->config as $configSectionValue)
        {
            foreach($configSectionValue as $configKey => $configValue)
            {
                // Disabled in the config so skip complete section
                if(strtolower($configKey) == 'enabled' && !$configValue)
                {
                    continue 2;
                }

                if(strtolower($configKey) == 'url')
                {
                    $parsedURL = parse_url($configValue);

                    if ($parsedURL['host'] != $hostname)
                    {
                        $dnsPrefetchRecords[] = $parsedURL['scheme'] . '://' . $parsedURL['host'];
                    }
                }
            }
        }

        return $dnsPrefetchRecords;
    }
}