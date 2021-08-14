<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Torrents\Torrents;
use Chell\Models\Torrents\qBittorrent;
use Chell\Models\Torrents\Transmission;

/**
 * The controller responsible for all Torrents widget related actions.
 * Currently just used as a stub, to indicate this widget exists.
 *
 * @package Controllers
 */
class TorrentsController extends WidgetController
{
    private Torrents $_client;

    public function initialize()
    {
        parent::initialize();

        switch($this->settings->torrents->client)
        {
            case 'qbittorrent':
                $this->_client = new qBittorrent();
                break;

            case 'transmission':
                $this->_client = new Transmission();
                break;
        }

        $this->view->disable();
    }

    public function indexAction()
    {
        $this->response->setJsonContent($this->_client->getTorrents())->send();
    }

    public function pauseAction($id)
    {
        $this->_client->pauseTorrent($id);
    }

    public function resumeAction($id)
    {
        $this->_client->resumeTorrent($id);
    }

    public function removeAction($id)
    {
        $this->_client->removeTorrent($id);
    }
}