<?php

class KodiTVShows extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('tvshow');

        $this->hasMany(
            'idShow',
            'KodieTVShowEpisodes',
            'idShow',
            array('alias' => 'episodes')
        );
    }
}