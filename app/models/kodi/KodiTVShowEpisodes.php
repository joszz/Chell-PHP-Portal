<?php

namespace Chell\Models\Kodi;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all Kodi episodes.
 *
 * @package Models\Kodi
 */
class KodiTVShowEpisodes extends Model
{
    /**
     * Sets the right DB connection and sets the table/view to album
     */
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('episode_view');
    }

    /**
     * Gets the latest episodes added to the Kodi DB.
     *
     * @param int $limit    Amount of episodes to retrieve, defaults to 10
     * @return array        The array of Kodi episode
     */
    public static function getLatestEpisodes($limit = 10)
    {
        return self::extractMovieImagesFromXML(self::find(array('order' => 'idEpisode DESC', 'limit' => $limit)));
    }

    /**
     * Extracts thumbs from the XML stored in the DB.
     *
     * @param array $movies     The array of Kodi episodes.
     * @return array            The array of Kodi episodes with the XML field transformed to string holding only image URL.
     */
    public static function extractMovieImagesFromXML($episodes)
    {
        $return = array();

        foreach($episodes as $episode)
        {
            $episode->c06 = substr($episode->c06, $start = strpos($episode->c06, '>') + 1, strpos($episode->c06, '<', $start) - $start);
            $episode->c06 = current(explode('?', $episode->c06));   //Remove any query parameters from the string

            $return[] = $episode;
        }

        return $return;
    }
}