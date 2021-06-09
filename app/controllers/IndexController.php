<?php

namespace Chell\Controllers;

use Chell\Models\Couchpotato;
use Chell\Models\Devices;
use Chell\Models\Jellyfin;
use Chell\Models\Motion;
use Chell\Models\Kodi\KodiMovies;
use Chell\Models\Kodi\KodiAlbums;
use Chell\Models\Kodi\KodiTVShowEpisodes;
use Chell\Models\Settings;
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
            $this->addJs('dashboard', 'js/dashboard-blocks/couchpotato.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/devices.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/gallery.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/hyperv-admin.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/motion.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/nowplaying.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/opcache.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/phpsysinfo.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/pihole.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/sickrage.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/speedtest.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/transmission.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/youless.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/snmp.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/verisure.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/roborock.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/rcpu.js');
            $this->addJs('dashboard', 'js/dashboard-blocks/pulseway.js');
            $this->addJs('dashboard', 'js/dashboard.js');
        }
        else
        {
            $this->addJs('dashboard', 'js/dashboard.min.js');
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
        $this->response->setContentType('application/json', 'charset=UTF-8');
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    /**
     * The stub service worker. Setting header for allowing service worker from root.
     */
    public function workerAction()
    {
        $this->response->setHeader('Service-Worker-allowed', '../');
        $this->response->setContentType('text/javascript');
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
        return Settings::count('name = "enabled" AND value = "1"') > 0;
    }
}