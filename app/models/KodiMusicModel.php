<?php

class KodiMusicModel extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiMusic');
        $this->setSource('album');
    }

    public static function getLatestAlbums($limit = 10){
        return self::extractAlbumImagesFromXML(self::find(array('order' => 'idAlbum DESC', 'limit' => $limit))->toArray());
    }

    private static function extractAlbumImagesFromXML($albums)
    {
        $albumCount = count($albums);
        
        for($i = 0; $i < $albumCount; $i++)
        {
            $albums[$i]['thumb'] = substr($albums[$i]['strImage'], $start = strpos($albums[$i]['strImage'], '>') + 1, strpos($albums[$i]['strImage'], '<', $start) - $start);
            $albums[$i]['thumb'] = str_replace('http://', 'https://', $albums[$i]['thumb']);
        }
        
        return $albums;
    }
}