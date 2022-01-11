<?php

namespace Chell\Models\Kodi;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all Kodi episodes.
 *
 * @package Models\Kodi
 * @suppress PHP2414
 */
class KodiFiles extends KodiBase
{
    /**
     * Sets the right DB connection and sets the table/view to album
     */
    public function initialize()
    {
        $this->setConnectionService('dbKodiVideo');
        $this->setSource('files');

        $this->belongsTo(
            'idFile',
            'Chell\Models\Kodi\KodiTVShowEpisodes',
            'idFile',
            ['alias' => 'episodes']
        );

        $this->belongsTo(
            'idFile',
            'Chell\Models\Kodi\KodiMovies',
            'idFile',
            ['alias' => 'movies']
        );
    }
}