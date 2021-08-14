<?php

namespace Chell\Models;

use stdClass;

class Transmission extends Torrents
{
	private string $_transmissionSessionId;

    public function getTorrents()
    {
		$curl = $this->getCurl('{"method":"torrent-get", "arguments":{"fields":["id", "name", "percentDone", "status"]}}');
		$torrents = json_decode(curl_exec($curl))->arguments->torrents;
		curl_close($curl);
		$result = [];

        foreach($torrents as $torrent)
        {
            $formatted = new stdClass();
            $formatted->id = $torrent->id;
            $formatted->percentDone = $torrent->percentDone;
            $formatted->name = $torrent->name;
			$formatted->status = $torrent->status;

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

    public function resumeTorrent($torrentId)
    {
		$curl = $this->getCurl('{"method":"torrent-start-now", "arguments":{"ids":[' . $torrentId . ']}}');
		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
    }

    public function pauseTorrent($torrentId)
    {
		$curl = $this->getCurl('{"method":"torrent-stop", "arguments":{"ids":[' . $torrentId . ']}}');
		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
    }

    public function removeTorrent($torrentId)
    {
		$curl = $this->getCurl('{"method":"torrent-remove", "arguments":{"ids":[' . $torrentId . ']}, "delete-local-data": true}');
		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
    }

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