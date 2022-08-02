<?php

namespace Chell\Models;

use stdClass;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * @see https://github.com/Sonarr/Sonarr/wiki/API
 */
class Sonarr extends BaseModel
{
    public function getCalender($start, $end)
    {
        $episodes = $this->getHttpClient('calendar', '&start=' . $start . '&end=' . $end);
        $episodes = json_decode($episodes->getBody());
        $series = $this->getSeries();
        $result = [];

        foreach ($episodes as $episode)
        {
            $serie = current(array_filter($series, fn($serie) =>  $serie->id == $episode->seriesId));
            $entry = new stdClass();
            $entry->start = $entry->end = $episode->airDate;
            $entry->serie = $serie->title;
            $entry->title = $episode->title;
            $entry->seasonNumber = $episode->seasonNumber;
            $entry->episodeNumber = $episode->episodeNumber;
            $result[] = $entry;
        }

        return $result;
    }

    public function getSeries()
    {
        $series = $this->getHttpClient('series');
        $series = json_decode($series->getBody());
        return $series;
    }

    private function getHttpClient(string $url, string $parameters = '') : ResponseInterface
    {
        $client = new Client();
        return $client->request('GET', 'http://192.168.1.30:8989/api/v3/' . $url . '?apikey=b356ab98ec344e149fb29a55d7f2a5d6' . $parameters);
    }
}