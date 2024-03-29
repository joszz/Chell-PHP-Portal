<?php

namespace Chell\Models\Torrents;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to qBittorrent.
 *
 * @package Models\Torrents
 * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)
 */
class qBittorrent extends Torrents
{
    private $_sid;

    /**
     * Retrieves torrents from the qBittorrent API.
     *
     * @return object[]     The formatted torrent information
     */
    public function getTorrents()
    {
        $response = $this->getHttpClient('torrents/info');
        $torrents = json_decode($response->getBody());
        $result = [];

        foreach ($torrents as $torrent)
        {
            $formatted = (object)[
                'id'            => $torrent->hash,
                'percentDone'   => $torrent->progress,
                'name'          => $torrent->name,
                'status'        => $torrent->state
            ];

            if ($torrent->state == 'pausedDL')
            {
                $formatted->status = 'paused';
            }

            $result[] = $formatted;
        }

        return $result;
    }

    /**
     * Resumes a torrent by given torrentId/hash.
     *
     * @param string $torrentId  The torrent to resume.
     */
    public function resumeTorrent(string $torrentId)
    {
        $this->getHttpClient('torrents/resume', ['hashes' => $torrentId]);
    }

    /**
     * Pauses a torrent by given torrentId/hash.
     *
     * @param string $torrentId  The torrent to pause.
     */
    public function pauseTorrent(string $torrentId)
    {
        $this->getHttpClient('torrents/pause', ['hashes' => $torrentId]);
    }

    /**
     * Deletes a torrent and the files by given torrentId/hash.
     *
     * @param string $torrentId  The torrent to delete.
     */
    public function removeTorrent(string $torrentId)
    {
        $this->getHttpClient('torrents/delete', [
            'hashes' => $torrentId,
            'deleteFiles' => 'true',
        ]);
    }

    /**
     * Authenticates the server to the qBittorrent API. A cookie with a sid key will be set, intercept the cookie to use later for credentials.
     */
    protected function authenticate()
    {
        $response = $this->getHttpClient('auth/login', [
            'username' => $this->settings->torrents->username->value,
            'password' => $this->settings->torrents->password->value,
        ]);
        $headers = $response->getHeaders();
        $cookies = explode(';', current($headers['set-cookie']));

        foreach ($cookies as $cookie)
        {
            $parts = explode('=', $cookie);

            if (strtolower($parts[0]) == 'sid')
            {
                $this->_sid = $parts[1];
            }
        }
    }

    /**
     * Retrieves a ResponseInterface instance to use to communicate with the qBittorrent API. Sets the SID cookie for authentication.
     *
     * @param string $url           The API part of the URL to request.
     * @return ResponseInterface    The Guzzle handle to use to communicate with the qBittorrent API.
     */
    private function getHttpClient(string $url, array $formParams = []) : ResponseInterface
    {
        $client = new Client($this->_sid ? ['headers' => ['Cookie' => 'SID=' . $this->_sid]] : []);

        if (!count($formParams))
        {
            return $client->request('GET', $this->settings->torrents->url->value . 'api/v2/' . $url);
        }

        return $client->request('POST', $this->settings->torrents->url->value . 'api/v2/' . $url, [
            'form_params' => $formParams
        ]);
    }
}