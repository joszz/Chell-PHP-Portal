<?php

namespace Chell\Models\Kodi;

use Phalcon\Mvc\Model\ResultsetInterface;

/**
 * The model responsible for all Kodi episodes.
 *
 * @package Models\Kodi
 * @suppress PHP2414
 */
class KodiTVShowEpisodes extends KodiBase
{
    /**
     * Sets the right DB connection and sets the table/view to album
     */
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('episode_view');

        $this->hasMany(
            'idFile',
            'Chell\Models\Kodi\KodiFiles',
            'idFile',
            ['alias' => 'files']
        );

        $this->belongsTo(
            'idShow',
            'Chell\Models\Kodi\KodiTVShow',
            'idShow',
            ['alias' => 'show']
        );
    }

    /**
     * Gets the latest episodes added to the Kodi DB.
     *
     * @param int $limit    Amount of episodes to retrieve, defaults to 10
     * @return array        The array of Kodi episode
     */
    public function getLatestEpisodes(int $limit = 10) : array
    {
        return $this->extractMovieImagesFromXML(self::find(['order' => 'dateAdded DESC', 'limit' => $limit]));
    }

    /**
     * Extracts thumbs from the XML stored in the DB.
     *
     * @param ResultsetInterface|array $movies    The array of Kodi episodes.
     * @return array                              The array of Kodi episodes with the XML field transformed to string holding only image URL.
     */
    public function extractMovieImagesFromXML(ResultsetInterface $episodes) : array
    {
        $return = [];

        foreach ($episodes as $episode)
        {
            if (!empty($episode->c06))
            {
                $xml = self::getXml($episode->c06);
                $episode->c06 = (string)$xml->thumb[rand(0, count($xml->thumb) - 1)];
            }

            $return[] = $episode;
        }

        return $return;
    }
}