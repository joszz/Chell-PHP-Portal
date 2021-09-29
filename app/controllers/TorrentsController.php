<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Torrents\Torrents;
use Chell\Models\Torrents\qBittorrent;
use Chell\Models\Torrents\Transmission;
use Chell\Models\Widget;

/**
 * The controller responsible for all Torrents widget related actions.
 * Currently just used as a stub, to indicate this widget exists.
 *
 * @package Controllers
 */
class TorrentsController extends WidgetController
{
    private Torrents $_client;

    /**
     * Sets the correct Torrent client based on saved settings.
     */
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

    public function setPanelSize()
    {
        $this->widget = new Widget(12);
    }

    /**
     * Retrieves a list of torrents.
     */
    public function indexAction()
    {
        $this->response->setJsonContent($this->_client->getTorrents())->send();
    }

    /**
     * Pauses a torrent.
     *
     * @param mixed $id     The torrent Id to pause.
     */
    public function pauseAction($id)
    {
        $this->_client->pauseTorrent($id);
    }

    /**
     * Resumes a torrent.
     *
     * @param mixed $id     The torrent Id to resume.
     */
    public function resumeAction($id)
    {
        $this->_client->resumeTorrent($id);
    }

    /**
     * Deletes a torrent.
     *
     * @param mixed $id     The torrent Id to delete.
     */
    public function removeAction($id)
    {
        $this->_client->removeTorrent($id);
    }
}