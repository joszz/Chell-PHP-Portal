<?php

namespace Chell\Models\Kodi;

use Phalcon\Mvc\Model\ResultsetInterface;

/**
 * The model responsible for all Kodi episodes.
 *
 * @package Models\Kodi
 * @suppress PHP2414
 */
class KodiTVShow extends KodiBase
{
    /**
     * Sets the right DB connection and sets the table/view to album
     */
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('tvshow_view');

        $this->hasMany(
            'idShow',
            'Chell\Models\Kodi\KodiTVShowEpisodes',
            'idShow',
            ['alias' => 'episodes']
        );
    }

    /**
     * Extracts thumbs from the XML stored in the DB.
     *
     * @param ResultsetInterface|array $movies    The array of Kodi episodes.
     * @return array                              The array of Kodi episodes with the XML field transformed to string holding only image URL.
     */
    public static function extractMovieImagesFromXML(ResultsetInterface|array $shows) : array
    {
        $return = [];

        foreach ($shows as $show)
        {
            if (!empty($show->c06))
            {
                $xml = self::getXml($show->c06);
                $show->c06 = (string)$xml->thumb[rand(0, count($xml->thumb) - 1)];
            }

            $return[] = $show;
        }

        return $return;
    }
}