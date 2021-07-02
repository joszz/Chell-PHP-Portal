<?php

namespace Chell\Controllers;

use ReflectionClass;
use DirectoryIterator;
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
     * Shows the dashboard view
     */
    public function indexAction()
    {
        $this->setAssets();

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

    private function setAssets()
    {
        $dir = new DirectoryIterator(APP_PATH . 'app/controllers/');
        $scripts = [
            'dashboard',
            'jquery.isloading',
            'jquery.tinytimer',
        ];
        $styles = ['dashboard'];

        foreach ($dir as $fileinfo)
        {
            $file = $fileinfo->getFilename();
            $controllerName = __NAMESPACE__ . '\\' . basename($file, '.' . $fileinfo->getExtension());
            $currentClass = [get_class($this), get_parent_class($this)];

            if (!$fileinfo->isDot() && !in_array($controllerName, $currentClass))
            {
                $controller = new ReflectionClass($controllerName);

                if ($controller->isSubclassOf(__NAMESPACE__ . '\WidgetController'))
                {
                    $name = strtolower(str_replace('Controller', '', str_replace(__NAMESPACE__ . '\\', '', $controller->getName())));
                    $controllerInstance = $controller->newInstanceWithoutConstructor();
                    $jsFilesProperty = $controller->getProperty('jsFiles');
                    $jsFilesProperty->setAccessible(true);
                    $scripts = array_merge($jsFilesProperty->getValue($controllerInstance), $scripts);

                    $cssFilesProperty = $controller->getProperty('cssFiles');
                    $cssFilesProperty->setAccessible(true);
                    $styles =  array_merge($cssFilesProperty->getValue($controllerInstance), $styles);

                    if (isset($this->settings->{$name}) && $this->settings->{$name}->enabled || !isset($this->settings->{$name}))
                    {
                        $scripts[] = $name;
                        $styles[] = $name;
                    }
                }
            }
        }

        $this->assets->addScripts($scripts)->addStyles($styles);
    }
}