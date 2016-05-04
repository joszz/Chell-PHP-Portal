<?php

class KodiVideoModel extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('movie');
    }

    public static function getLatestMovies($limit = 10){
        return self::extractMovieImagesFromXML(self::find(array('order' => 'idMovie DESC', 'limit' => $limit))->toArray());
    }

    private static function extractMovieImagesFromXML($movies)
    {
        $movieCount = count($movies);
        
        for($i = 0; $i < $movieCount; $i++)
        {
            $movies[$i]['thumb'] = substr($movies[$i]['c08'], $start = strpos($movies[$i]['c08'], 'preview=') + 9, strpos($movies[$i]['c08'], '"', $start) - $start);
            $movies[$i]['thumb'] = str_replace('http://', 'https://', $movies[$i]['thumb']);

            if(!empty($movies[$i]['c20']))
            {
                $movies[$i]['fanart'] = substr($movies[$i]['c20'], $start = strpos($movies[$i]['c20'], '>http://') + 1, strpos($movies[$i]['c20'], '</', $start) - $start);
            }
            else
            {
                $movies[$i]['fanart'] = '';
            }
        }
        
        return $movies;
    }
}