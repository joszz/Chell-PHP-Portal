<?php

namespace Chell\Models;

use stdClass;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to Radarr and Sonarr.
 * 
 * @see https://github.com/Sonarr/Sonarr/wiki/API
 * @package Models
 * @suppress PHP2414
 */
class Arr extends BaseModel
{
    /**
     * Retrieves Radarr and Sonarr calendar entries if the plugins are enabled.
     * Sorted by startdate.
     *
     * @param string $start     The start date as string, format 'yyyy-mm-dd'
     * @param string $end       The end date as string, format 'yyyy-mm-dd'
     * @return array<stdClass>  The Radarr and Sonarr entries as an array.
     */
    public function getCalendar(string $start, string $end) : array
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

    /**
     * Retrieves Sonarr calendar entries for the given $start and $end range.
     *
     * @param string $start     The start date as string, format 'yyyy-mm-dd'
     * @param string $end       The end date as string, format 'yyyy-mm-dd'
     * @return array<stdClass>  An array of anonymous objects representing Sonarr calendar entries.
     */
    private function getSonarrCalendar(string $start, string $end) : array
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

    /**
     * Retrieves all series defined in Sonarr.
     *
     * @return array    All series
     */
    private function getSonarrSeries()
    {
        $series = $this->getHttpClient($this->_settings->sonarr->url . 'series', $this->_settings->sonarr->api_key);
        $series = json_decode($series->getBody());
        return $series;
    }

    /**
     * Retrieves Radarr calendar entries for the given $start and $end range.
     *
     * @param string $start     The start date as string, format 'yyyy-mm-dd'
     * @param string $end       The end date as string, format 'yyyy-mm-dd'
     * @return array<stdClass>  An array of anonymous objects representing Radarr calendar entries.
     */
    private function getRadarrCalendar(string $start, string $end)
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

    /**
     * Gets the ResponseInterface to be used to invoke the Sonarr and Radarr API.
     *
     * @param string $url           The Sonarr or Radarr endpoint to call.
     * @param string $apiKey        The Sonarr or Radarr API key to authenticate with.
     * @param string $parameters    The Sonarr or Radarr querystring parameters.
     * @return ResponseInterface    The ResponseInterface to call the API with.
     */
    private function getHttpClient(string $url, string $apiKey, string $parameters = '') : ResponseInterface
    {
        $client = new Client();
        return $client->request('GET', $url . '?apikey=' . $apiKey . $parameters);
    }
}
