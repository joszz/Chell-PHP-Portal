<?php

namespace Chell\Controllers;

use Chell\Models\Couchpotato;
use Chell\Models\Devices;
use Chell\Models\Jellyfin;
use Chell\Models\Motion;
use Chell\Models\Kodi\KodiMovies;
use Chell\Models\Kodi\KodiAlbums;
use Chell\Models\Kodi\KodiTVShowEpisodes;
use Chell\Models\SnmpHosts;
use Phalcon\Mvc\View;

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

        if (DEBUG)
        {
            //$this->assets->collection('dashboard')->addJs('typescriptjs/dashboard.js' , true, false, ['defer' => 'defer', 'type' => 'module'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/couchpotato.js' , true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/devices.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/gallery.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/hyperv-admin.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/motion.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/nowplaying.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/opcache.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/phpsysinfo.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/pihole.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/sickrage.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/speedtest.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/transmission.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/youless.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/snmp.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/verisure.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/roborock.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/rcpu.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard-blocks/pulseway.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('dashboard')->addJs('js/dashboard.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
        }
        else
        {
            $this->assets->collection('dashboard')->addJs('js/dashboard.min.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
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

        if ($this->settings->kodi->enabled)
        {
            $this->view->movies = (new KodiMovies())->getLatestMovies();
            $this->view->albums = (new KodiAlbums())->getLatestAlbums();
            $this->view->episodes = (new KodiTVShowEpisodes())->getLatestEpisodes();
        }

        if ($this->settings->jellyfin->enabled)
        {
            $jellyfin = new Jellyfin();
            $views = explode(',', $this->settings->jellyfin->views);
            $jellyfinviews = [];

            foreach ($views as $view)
            {
                list($title, $viewId) = explode(':', $view);
                $jellyfinviews[strtolower($title)] = $jellyfin->getLatestForView($viewId);
            }

            $this->view->jellyfinviews = $jellyfinviews;
        }

        if ($this->settings->couchpotato->enabled)
        {
            $this->view->couchpotato = (new Couchpotato())->getAllMovies();
        }

        if ($this->settings->motion->enabled)
        {
            $this->view->motionModifiedTime = (new Motion())->getModifiedTime();
        }

        if ($this->settings->snmp->enabled)
        {
            $this->view->snmpHosts = SnmpHosts::find();
        }
    }

    /**
     * Renders the manifest.json file, used for favicons and the like.
     */
    public function manifestAction()
    {
        header('Content-Type: application/json; charset=UTF-8');
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
    private function setDNSPrefetchRecords() : array
    {
        $hostname = getenv('HTTP_HOST');
        $dnsPrefetchRecords = $this->settings->couchpotato->enabled ? [$this->settings->couchpotato->tmdb_api_url] : [];

        foreach($this->settings as $settingSectionValue)
        {
            foreach($settingSectionValue as $settingKey => $settingValue)
            {
                // Disabled in the config so skip complete section
                if (strtolower($settingKey) == 'enabled' && !$settingValue)
                {
                    continue 2;
                }

                if (strtolower($settingKey) == 'url')
                {
                    $parsedURL = parse_url($settingValue);

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
     *
     * @return bool     Whether any of the widgets is enabled.
     */
    private function getAnyWidgetEnabled() : bool
    {
        return $this->settings->phpsysinfo->enabled || count($this->view->devices) || $this->settings->rcpu->enabled || $this->settings->transmission->enabled ||
            $this->settings->kodi->enabled || $this->settings->subsonic->enabled || $this->settings->couchpotato->enabled || $this->settings->motion->enabled ||
            $this->settings->speedtest->enabled || $this->settings->sickrage->enabled || $this->settings->opcache->enabled || $this->settings->pihole->enabled ||
            $this->settings->snmp->enabled || $this->settings->verisure->enabled || $this->settings->youless->enabled;
    }
}