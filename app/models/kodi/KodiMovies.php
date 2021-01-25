<?php

namespace Chell\Models\Kodi;


/**
 * The model responsible for all Kodi movies.
 *
 * @package Models\Kodi
 */
class KodiMovies extends KodiBase
{
    /**
     * Sets the right DB connection and sets the table/view to movie_view
     */
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('movie_view');

        $this->hasMany(
            'idFile',
            'Chell\Models\Kodi\KodiFiles',
            'idFile',
            ['alias' => 'files']
        );
    }

    /**
     * Gets the latest movies added to the Kodi DB.
     *
     * @param int $limit    Amount of movies to retrieve, defaults to 10
     * @return array        The array of Kodi movies
     */
    public static function getLatestMovies($limit = 10)
    {
        return self::extractMovieImagesFromXML(self::find(['order' => 'dateAdded DESC', 'limit' => $limit]));
    }

    /**
     * Extracts thumbs and fanart from the XML stored in the DB.
     *
     * @param array|\Phalcon\Mvc\Model\ResultsetInterface $movies   The array of Kodi movies.
     * @return array                                                The array of Kodi movies with the XML fields transformed to strings holding only image URLs.
     */
    public static function extractMovieImagesFromXML($movies)
    {
        $return = [];

        foreach ($movies as $movie)
        {
            $xml = self::getXml($movie->c08);
            $movie->c08 = (string)$xml->thumb[rand(0, count($xml->thumb) - 1)]['preview'];

            $xml = self::getXml($movie->c20);
            $movie->c20 = count($xml->fanart->thumb) > 0 ? (string)$xml->fanart->thumb[rand(0, count($xml->fanart->thumb) - 1)] : '';

            $return[] = $movie;
        }

        return $return;
    }
}