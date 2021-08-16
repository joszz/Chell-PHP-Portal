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

    protected function getCurlHeaders($curl, $header, &$headers)
    {
        $length = strlen($header);
        $header = explode(':', $header, 2);
        // ignore invalid headers
        if (count($header) < 2)
        {
            return $length;
        }

        $headers[strtolower(trim($header[0]))] = trim($header[1]);
        return $length;
    }

    protected abstract function authenticate();

    public abstract function getTorrents();

    public abstract function resumeTorrent($torrentId);

    public abstract function pauseTorrent($torrentId);

    public abstract function removeTorrent($torrentId);
}