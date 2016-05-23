<?php

class KodiFiles extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('files');

        $this->belongsTo(
            'idFile',
            'KodiMovies',
            'c23',
            array('alias' => 'file')
        );
    }
}