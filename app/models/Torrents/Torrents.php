<?php
namespace Chell\Models\Torrents;

use Chell\Models\BaseModel;

/**
 * The abstract class for all Torrent client implementations.
 *
 * @package Models\Torrents
 */
abstract class Torrents extends BaseModel
{
    /**
     * Initializes the model, authenticating with the API.
     */
    public function initialize()
    {
        parent::initialize();

        $this->authenticate();
    }

    /**
     * Do authentication.
     */
    protected abstract function authenticate();

    /**
     * Get all active torrents.
     */
    public abstract function getTorrents();

    /**
     * Resume a torrent by it's Id.
     *
     * @param string $torrentId     The Id of the torrent to resume.
     */
    public abstract function resumeTorrent(string $torrentId);

    /**
     * Pause a torrent by it's Id.
     *
     * @param string $torrentId     The Id of the torrent to pause.
     */
    public abstract function pauseTorrent(string $torrentId);

    /**
     * Remove a torrent by it's Id.
     *
     * @param string $torrentId     The Id of the torrent to remove.
     */
    public abstract function removeTorrent(string $torrentId);
}