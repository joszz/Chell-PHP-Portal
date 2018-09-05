<?php

namespace Chell\Models\Kodi;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all Kodi movies.
 *
 * @package Models\Kodi
 */
class KodiMovies extends Model
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
            array('alias' => 'files')
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
        return self::extractMovieImagesFromXML(self::find(array('order' => 'idMovie DESC', 'limit' => $limit)));
    }

    /**
     * Extracts thumbs and fanart from the XML stored in the DB.
     *
     * @param array $movies     The array of Kodi movies.
     * @return array            The array of Kodi movies with the XML fields transformed to strings holding only image URLs.
     */
    public static function extractMovieImagesFromXML($movies)
    {
        $return = array();

        foreach ($movies as $movie)
        {
            $start = strpos($movie->c08, 'preview=') + 9;

            if ($start !== false && strlen($movie->c08) > $start)
            {
                $end = strpos($movie->c08, '"', $start);

                if ($end !== false)
                {
                    $end -= $start;
                    $movie->c08 = substr($movie->c08, $start, $end);
                }
            }

            if (!empty($movie->c20))
            {
                $start = strpos($movie->c20, '>http://') + 1;

                if ($start !== false)
                {
                    $end = strpos($movie->c20, '</', $start) - $start;

                    if ($end !== false)
                    {
                        $movie->c20 = substr($movie->c20, $start, $end);
                        $movie->c20 = current(explode('?', $movie->c20));
                    }
                }
            }

            $return[] = $movie;
        }

        return $return;
    }
}