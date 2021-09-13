<?php

namespace Chell\Models\Torrents;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to Transmission.
 *
 * @package Models\Torrents
 */
class Transmission extends Torrents
{
	private string $_transmissionSessionId;

    /**
     * Retrieves torrents from the Transmission API.
     *
     * @return object[]     The formatted torrent information
     */
    public function getTorrents()
    {
		$response = $this->getHttpClient('{"method":"torrent-get", "arguments":{"fields":["id", "name", "percentDone", "status"]}}');
		$torrents = json_decode($response->getBody())->arguments->torrents;
		$result = [];

        foreach($torrents as $torrent)
        {
            $formatted = (object)[
                'id'            => $torrent->id,
                'percentDone'   => $torrent->percentDone,
                'name'          => $torrent->name,
                'status'        => $torrent->status
            ];

			if ($torrent->status == 4)
            {
                $formatted->status = 'downloading';
            }
			else if ($torrent->status == 0)
            {
                $formatted->status = 'paused';
            }

            $result[] = $formatted;
        }

		return $result;
    }

    /**
     * Resumes a torrent by given torrentId.
     *
     * @param mixed $torrentId  The torrent to resume.
     */
    public function resumeTorrent($torrentId)
    {
		$response = $this->getHttpClient('{"method":"torrent-start-now", "arguments":{"ids":[' . $torrentId . ']}}');
		return $response->getBody();;
    }

    /**
     * Pauses a torrent by given torrentId.
     *
     * @param mixed $torrentId  The torrent to pause.
     */
    public function pauseTorrent($torrentId)
    {
		$response = $this->getHttpClient('{"method":"torrent-stop", "arguments":{"ids":[' . $torrentId . ']}}');
		return $response->getBody();
    }

    /**
     * Deletes a torrent and the files by given torrentId.
     *
     * @param mixed $torrentId  The torrent to delete.
     */
    public function removeTorrent($torrentId)
    {
		$response = $this->getHttpClient('{"method":"torrent-remove", "arguments":{"ids":[' . $torrentId . ']}, "delete-local-data": true}');
		return $response->getBody();
    }

    /**
     * Authenticates the server to the Transmission API.
     * When the HTTP status code == 409, retrieve the Transmission session Id, to use later when communicating with the API.
     */
	protected function authenticate()
    {
        $client = new Client();
        $response = $client->request('GET', $this->_settings->torrents->url . '/rpc/',
			['auth' => [$this->_settings->torrents->username , $this->_settings->torrents->password]]);

		if ($response->getStatusCode() == 409)
        {
            $this->_transmissionSessionId = current($response->getHeaders()['x-transmission-session-id']);
		}
    }

    /**
     * Retrieves a ResponseInterface instance to use to communicate with the Transmission API. Sets the X-Transmission-Session-Id for CSRF protection.
     *
     * @param string $url           The API part of the URL to request.
     * @return ResponseInterface    The Guzzle handle to use to communicate with the Transmission API.
     */
    private function getHttpClient($postFields) : ResponseInterface
    {
        $client = new Client(['headers' => ['X-Transmission-Session-Id' => $this->_transmissionSessionId]]);
        return $client->request('POST', $this->_settings->torrents->url . '/rpc/', [
            'body' => $postFields
        ]);
    }
}