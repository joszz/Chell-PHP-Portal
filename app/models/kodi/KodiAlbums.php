<?php

namespace Chell\Models\Kodi;

/**
 * The model responsible for all Kodi albums.
 *
 * @package Models\Kodi
 */
class KodiAlbums extends KodiBase
{
    /**
     * Sets the right DB connection and sets the table/view to album
     */
    public function initialize()
    {
        $this->setConnectionService('dbKodiMusic');
        $this->setSource('albumview');
    }

    /**
     * Gets the latest albums added to the Kodi DB.
     *
     * @param int $limit    Amount of albums to retrieve, defaults to 10
     * @return array        The array of Kodi albums
     */
    public function getLatestAlbums($limit = 10)
    {
        return $this->extractAlbumImagesFromXML(self::find(['order' => 'dateAdded DESC', 'limit' => $limit]));
    }

    /**
     * Extracts thumbs from the XML stored in the DB.
     *
     * @param array $movies The array of Kodi albums.
     * @return array        The array of Kodi albums with the XML field transformed to string holding only image URL.
     */
    public function extractAlbumImagesFromXML($albums)
    {
        $return = [];

        foreach ($albums as $album)
        {
            if (!empty($album->strImage))
            {
                $xml = self::getXml($album->strImage);
                $album->strImage = (string)$xml->thumb[rand(0, count($xml->thumb) - 1)];
            }

            $return[] = $album;
        }

        return $return;
    }
}