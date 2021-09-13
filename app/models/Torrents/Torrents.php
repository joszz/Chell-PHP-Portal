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
     * Authenticate with the API.
     */
    public function initialize()
    {
        parent::initialize();

        $this->authenticate();
    }

    protected abstract function authenticate();

    public abstract function getTorrents();

    public abstract function resumeTorrent($torrentId);

    public abstract function pauseTorrent($torrentId);

    public abstract function removeTorrent($torrentId);
}