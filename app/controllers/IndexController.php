<?php

namespace Chell\Controllers;

use Phalcon\Mvc\View;

use Chell\Models\Couchpotato;
use Chell\Models\Devices;
use Chell\Models\Jellyfin;
use Chell\Models\Motion;
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
     * Initializes the controller, adding JS being used.
     */
    public function initialize()
    {
        parent::initialize();

        if ($this->config->application->debug)
        {
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/couchpotato.js' , true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/devices.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/gallery.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/hyperv-admin.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/motion.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/nowplaying.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/opcache.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/phpsysinfo.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/pihole.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/sickrage.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/speedtest.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/transmission.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/youless.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/snmp.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/verisure.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/roborock.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
        }
        else
        {
            $this->assets->collection('dashboard')->addJs('js/dashboard.min.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
        }
    }

    /**
     * Shows the dashboard view
     */
    public function indexAction()
    {
        $this->view->dnsPrefetchRecords = $this->setDNSPrefetchRecords();
        $this->view->devices = Devices::find(['order' => 'name ASC']);
        $this->view->anyWidgetEnabled = $this->getAnyWidgetEnabled();

        if ($this->config->kodi->enabled)
        {
            $this->view->movies = KodiMovies::getLatestMovies();
            $this->view->albums = KodiAlbums::getLatestAlbums();
            $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
        }

        if ($this->config->jellyfin->enabled)
        {
            Jellyfin::GetLatest($this->config, $this->view);
        }

        if ($this->config->couchpotato->enabled)
        {
            $this->view->couchpotato = Couchpotato::getAllMovies($this->config);
        }

        if ($this->config->motion->enabled)
        {
            $this->view->motionModifiedTime = Motion::getModifiedTime($this->config);
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
     * The stub service worker. Setting header for allowing service worker from root.
     */
    public function workerAction()
    {
        header('Service-Worker-allowed: ../');
        header('Content-Type: text/javascript');
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
                if (strtolower($configKey) == 'enabled' && !$configValue)
                {
                    continue 2;
                }

                if (strtolower($configKey) == 'url')
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

    /**
     * Is any of the widgets enabled?
     * @return bool     Whether any of the widgets is enabled.
     */
    private function getAnyWidgetEnabled()
    {
        return $this->config->phpsysinfo->enabled || count($this->view->devices) || $this->config->rcpu->enabled || $this->config->transmission->enabled ||
            $this->config->kodi->enabled || $this->config->subsonic->enabled || $this->config->couchpotato->enabled || $this->config->motion->enabled ||
            $this->config->speedtest->enabled || $this->config->sickrage->enabled || $this->config->opcache->enabled || $this->config->pihole->enabled ||
            $this->config->snmp->enabled || $this->config->verisure->enabled || $this->config->youless->enabled;
    }
}