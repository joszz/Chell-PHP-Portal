<?php

class KodiTVShowEpisodes extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('episode_view');
    }

    public static function getLatestEpisodes($limit = 10){
        return self::extractMovieImagesFromXML(self::find(array('order' => 'idEpisode DESC', 'limit' => $limit)));
    }

    public static function extractMovieImagesFromXML($episodes)
    {
        $return = array();

        foreach($episodes as $episode)
        {
            $episode->c06 = substr($episode->c06, $start = strpos($episode->c06, '>') + 1, strpos($episode->c06, '<', $start) - $start);
            $episode->c06 = current(explode('?', $episode->c06));

            $return[] = $episode;
        }
        
        return $return;
    }
}