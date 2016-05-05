<?php

class KodiMovies extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('movie');
    }

    public static function getLatestMovies($limit = 10){
        return self::extractMovieImagesFromXML(self::find(array('order' => 'idMovie DESC', 'limit' => $limit)));
    }

    private static function extractMovieImagesFromXML($movies)
    {
        $return = array();

        foreach($movies as $movie)
        {
            $movie->c08 = substr($movie->c08, $start = strpos($movie->c08, 'preview=') + 9, strpos($movie->c08, '"', $start) - $start);
            //$movie->c08 = str_replace('http://', 'https://', $movie->c08);
            
            if(!empty($movie->c20))
            {
                $movie->c20 = substr($movie->c20, $start = strpos($movie->c20, '>http://') + 1, strpos($movie->c20, '</', $start) - $start);
            }

            $return[] = $movie;
        }
        
        return $return;
    }
}