<?php

class KodiMusic extends BaseModel
{
    public function initialize()
    {
        $this->setConnectionService('dbKodiMusic');
        $this->setSource('album');
    }

    public static function getLatestAlbums($limit = 10){
        return self::extractAlbumImagesFromXML(self::find(array('order' => 'idAlbum DESC', 'limit' => $limit)));
    }

    private static function extractAlbumImagesFromXML($albums)
    {
        $return = array();

        foreach($albums as $album)
        {
            if(!empty($album->strImage))
            {
                $album->strImage = substr($album->strImage, $start = strpos($album->strImage, '>') + 1, strpos($album->strImage, '<', $start) - $start);
                //$album->strImage = str_replace('http://', 'https://', $album->strImage);
            }

            $return[] = $album;
        }
        
        return $return;
    }
}