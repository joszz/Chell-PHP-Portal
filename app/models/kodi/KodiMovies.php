<?php

class KodiMovies extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('movie_view');
    }

    public static function getLatestMovies($limit = 10){
        return self::extractMovieImagesFromXML(self::find(array('order' => 'idMovie DESC', 'limit' => $limit)));
    }

    public static function extractMovieImagesFromXML($movies)
    {
        $return = array();

        foreach($movies as $movie)
        {
            $movie->c08 = substr($movie->c08, $start = strpos($movie->c08, 'preview=') + 9, strpos($movie->c08, '"', $start) - $start);
            
            if(!empty($movie->c20))
            {
                $movie->c20 = substr($movie->c20, $start = strpos($movie->c20, '>http://') + 1, strpos($movie->c20, '</', $start) - $start);
                $movie->c20 = current(explode('?', $movie->c20));
            }

            $return[] = $movie;
        }
        
        return $return;
    }
}