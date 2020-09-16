<?php

namespace Chell\Controllers;

use Phalcon\Mvc\View;

use Chell\Models\Couchpotato;
use Chell\Models\Devices;
use Chell\Models\Motion;
use Chell\Models\Youless;
use Chell\Models\Kodi\KodiMovies;
use Chell\Models\Kodi\KodiAlbums;
use Chell\Models\Kodi\KodiTVShowEpisodes;
use Chell\Models\SnmpHosts;

/**
 * The controller responsible for all dashboard related actions.
 *
 * @package Controllers
 */
class IndexController extends BaseController
{
    /**
     * Shows the dashboard view
     */
    public function indexAction()
    {
        $this->view->dnsPrefetchRecords = $this->setDNSPrefetchRecords();
        $this->view->devices = Devices::find(['order' => 'name ASC']);

        if ($this->config->kodi->enabled)
        {
            $this->view->movies = KodiMovies::getLatestMovies();
            $this->view->albums = KodiAlbums::getLatestAlbums();
            $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
        }

        if ($this->config->couchpotato->enabled)
        {
            $this->view->couchpotato = Couchpotato::getAllMovies($this->config);
        }

        if ($this->config->motion->enabled)
        {
            $this->view->motionModifiedTime = Motion::getModifiedTime($this->config);
        }

        if ($this->config->youless->enabled)
        {
            $this->view->youlessStats = (new Youless())->getCurrentStats($this->config);
        }

        if ($this->config->snmp->enabled)
        {
            $this->view->snmpHosts = SnmpHosts::find();
        }
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
        $dnsPrefetchRecords = [$this->config->application->tmdbAPIURL];

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

                    if (isset($parsedURL['host'] ) && $parsedURL['host'] != $hostname)
                    {
                        $dnsPrefetchRecords[] = $parsedURL['scheme'] . '://' . $parsedURL['host'];
                    }
                }
            }
        }

        return $dnsPrefetchRecords;
    }
}