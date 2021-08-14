<?php

namespace Chell\Models;

use stdClass;

class qBittorrent extends Torrents
{
    private $sid;

    public function getTorrents()
    {
        $curl = $this->getCurl('torrents/info');
        $torrents = json_decode(curl_exec($curl));
        curl_close($curl);
        $result = [];

        foreach($torrents as $torrent)
        {
            $formatted = new stdClass();
            $formatted->id = $torrent->hash;
            $formatted->percentDone = $torrent->progress;
            $formatted->name = $torrent->name;
            $formatted->status = $torrent->status;
			
            if ($torrent->status == 'pausedDL')
            {
                $formatted->status = 'paused';
            }

            $result[] = $formatted;
        }

        return $result;
    }

    public function resumeTorrent($torrentId)
    {
        $curl = $this->getCurl('torrents/resume?hashes=' . $torrentId);
        curl_exec($curl);
        curl_close($curl);
    }

    public function pauseTorrent($torrentId)
    {
        $curl = $this->getCurl('torrents/pause?hashes=' . $torrentId);
        curl_exec($curl);
        curl_close($curl);
    }

    public function removeTorrent($torrentId)
    {
        $curl = $this->getCurl('torrents/delete?hashes=' . $torrentId . '&deleteFiles=true');
        curl_exec($curl);
        curl_close($curl);
    }

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
                $this->sid = $parts[1];
            }
        }
    }

    private function getCurl($url)
	{
		$curl = curl_init($this->_settings->torrents->url . 'api/v2/' . $url);

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_COOKIE => 'SID=' . $this->sid
		]);

		return $curl;
	}
}