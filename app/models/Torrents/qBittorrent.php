<?php

namespace Chell\Models\Torrents;

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
        $curl = $this->getCurl('torrents/info');
        $torrents = json_decode(curl_exec($curl));
        curl_close($curl);
        $result = [];

        foreach($torrents as $torrent)
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
     * @param mixed $torrentId  The torrent to resume.
     */
    public function resumeTorrent($torrentId)
    {
        $curl = $this->getCurl('torrents/resume?hashes=' . $torrentId);
        curl_exec($curl);
        curl_close($curl);
    }

    /**
     * Pauses a torrent by given torrentId/hash.
     *
     * @param mixed $torrentId  The torrent to pause.
     */
    public function pauseTorrent($torrentId)
    {
        $curl = $this->getCurl('torrents/pause?hashes=' . $torrentId);
        curl_exec($curl);
        curl_close($curl);
    }

    /**
     * Deletes a torrent and the files by given torrentId/hash.
     *
     * @param mixed $torrentId  The torrent to delete.
     */
    public function removeTorrent($torrentId)
    {
        $curl = $this->getCurl('torrents/delete?hashes=' . $torrentId . '&deleteFiles=true');
        curl_exec($curl);
        curl_close($curl);
    }

    /**
     * Authenticates the server to the qBittorrent API. A cookie with a sid key will be set, intercept the cookie to use later for credentials.
     */
    protected function authenticate()
    {
        $curl = curl_init($this->_settings->torrents->url . 'api/v2/auth/login');
        $headers = [];

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => ['username' => $this->_settings->torrents->username, 'password' => $this->_settings->torrents->password],
			CURLOPT_HEADER => true,
			CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers) {
                return $this->getCurlHeaders($curl, $header, $headers);
            },
		]);

        curl_exec($curl);
        curl_close($curl);

        $cookies = explode(';', $headers['set-cookie']);
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
     * Retrieves a cUrl instance to use to communicate with the qBittorrent API. Sets the SID cookie for authentication.
     *
     * @param string $url                   The API part of the URL to request.
     * @return \CurlHandle|bool|resource    The cUrl handle to use to communicate with the qBittorrent API.
     */
    private function getCurl(string $url)
	{
		$curl = curl_init($this->_settings->torrents->url . 'api/v2/' . $url);

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_COOKIE => 'SID=' . $this->_sid
		]);

		return $curl;
	}
}