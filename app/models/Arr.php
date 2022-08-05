<?php

namespace Chell\Models;

use stdClass;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * @see https://github.com/Sonarr/Sonarr/wiki/API
 */
class Arr extends BaseModel
{
    public function getCalendar($start, $end)
    {
        $result = [];

        if ($this->_settings->sonarr->enabled)
        {
            $result = array_merge($result, $this->getSonarrCalendar($start, $end));
        }
        if ($this->_settings->radarr->enabled)
        {
            $result = array_merge($result, $this->getRadarrCalendar($start, $end));
        }

        uasort($result, fn($a, $b) => $a->start < $b->start ? -1 : 1);
        $result = array_values($result);

        return $result;
    }

    private function getSonarrCalendar($start, $end)
    {
        $episodes = $this->getHttpClient($this->_settings->sonarr->url . 'calendar', $this->_settings->sonarr->api_key, '&start=' . $start . '&end=' . $end);
        $episodes = json_decode($episodes->getBody());
        $series = $this->getSonarrSeries();
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
            $entry->type = 'serie';
            $result[] = $entry;
        }

        return $result;
    }

    private function getSonarrSeries()
    {
        $series = $this->getHttpClient($this->_settings->sonarr->url . 'series', $this->_settings->sonarr->api_key);
        $series = json_decode($series->getBody());
        return $series;
    }

    private function getRadarrCalendar($start, $end)
    {
        $movies = $this->getHttpClient($this->_settings->radarr->url . 'calendar', $this->_settings->radarr->api_key, '&start=' . $start . '&end=' . $end);
        $movies = json_decode($movies->getBody());
        $result = [];

        foreach ($movies as $movie)
        {
            $release = $movie->digitalRelease ?? $movie->physicalRelease ?? null;

            if ($release)
            {
                $entry = new stdClass();
                $entry->start = $entry->end = date('Y-m-d', strtotime( $release));
                $entry->title = $movie->title;
                $entry->type = 'movie';
                $result[] = $entry;
            }
        }

        return $result;
    }

    private function getHttpClient(string $url, string $apiKey, string $parameters = '') : ResponseInterface
    {
        $client = new Client();
        return $client->request('GET', $url . '?apikey=' . $apiKey . $parameters);
    }
}
