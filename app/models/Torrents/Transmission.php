<?php

namespace Chell\Models\Torrents;

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
		$curl = $this->getCurl('{"method":"torrent-get", "arguments":{"fields":["id", "name", "percentDone", "status"]}}');
		$torrents = json_decode(curl_exec($curl))->arguments->torrents;
		curl_close($curl);
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
		$curl = $this->getCurl('{"method":"torrent-start-now", "arguments":{"ids":[' . $torrentId . ']}}');
		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
    }

    /**
     * Pauses a torrent by given torrentId.
     *
     * @param mixed $torrentId  The torrent to pause.
     */
    public function pauseTorrent($torrentId)
    {
		$curl = $this->getCurl('{"method":"torrent-stop", "arguments":{"ids":[' . $torrentId . ']}}');
		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
    }

    /**
     * Deletes a torrent and the files by given torrentId.
     *
     * @param mixed $torrentId  The torrent to delete.
     */
    public function removeTorrent($torrentId)
    {
		$curl = $this->getCurl('{"method":"torrent-remove", "arguments":{"ids":[' . $torrentId . ']}, "delete-local-data": true}');
		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
    }

    /**
     * Authenticates the server to the Transmission API.
     * When the HTTP status code == 409, retrieve the Transmission session Id, to use later when communicating with the API.
     */
	protected function authenticate()
    {
		$headers = [];
        $curl = curl_init($this->_settings->torrents->url . 'rpc/');

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => true,
			CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers) {
                return $this->getCurlHeaders($curl, $header, $headers);
            },
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_USERPWD => $this->_settings->torrents->username . ':' . $this->_settings->torrents->password
		]);

		curl_exec($curl);
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($statusCode == 409)
        {
            $this->_transmissionSessionId = $headers['x-transmission-session-id'];
		}
    }

    /**
     * Retrieves a cUrl instance to use to communicate with the Transmission API. Sets the X-Transmission-Session-Id for CSRF protection.
     *
     * @param string $url                   The API part of the URL to request.
     * @return \CurlHandle|bool|resource    The cUrl handle to use to communicate with the Transmission API.
     */
    private function getCurl($postFields)
	{
		$curl = curl_init($this->_settings->torrents->url . 'rpc/');

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postFields,
			CURLOPT_USERPWD => $this->_settings->torrents->username . ':' . $this->_settings->torrents->password,
			CURLOPT_HTTPHEADER => ['X-Transmission-Session-Id: ' . $this->_transmissionSessionId]
		]);

		return $curl;
	}
}