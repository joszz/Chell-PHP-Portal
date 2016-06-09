<?php

/**
 * The model responsible for all Kodi albums.
 * 
 * @package Models\Kodi
 */
class KodiMusic extends BaseModel
{
    /**
     * Sets the right DB connection and sets the table/view to album
     */
    public function initialize()
    {
        $this->setConnectionService('dbKodiMusic');
        $this->setSource('album');
    }

    /**
     * Gets the latest albums added to the Kodi DB.
     * 
     * @param int $limit    Amount of albums to retrieve, defaults to 10
     * @return array        The array of Kodi albums
     */
    public static function getLatestAlbums($limit = 10)
    {
        return self::extractAlbumImagesFromXML(self::find(array('order' => 'idAlbum DESC', 'limit' => $limit)));
    }

    /**
     * Extracts thumbs from the XML stored in the DB.
     * 
     * @param array $movies     The array of Kodi albums.
     * @return array            The array of Kodi albums with the XML field transformed to string holding only image URL.
     */
    public static function extractAlbumImagesFromXML($albums)
    {
        $return = array();

        foreach($albums as $album)
        {
            if(!empty($album->strImage))
            {
                $album->strImage = substr($album->strImage, $start = strpos($album->strImage, '>') + 1, strpos($album->strImage, '<', $start) - $start);
                $album->strImage = current(explode('?', $album->strImage));
            }

            $return[] = $album;
        }
        
        return $return;
    }
}