<?php

class KodiTVShowSeasons extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('seasons');

        $this->hasMany(
            'idSeason',
            'KodieTVShowEpisodes',
            'idSeason',
            array('alias' => 'season')
        );
    }
}