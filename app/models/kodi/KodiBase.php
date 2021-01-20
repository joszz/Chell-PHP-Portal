<?php

namespace Chell\Models\Kodi;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all Kodi movies.
 *
 * @package Models\Kodi
 */
class KodiBase extends Model
{
    /**
     * Retrieves the image URL, used in the src attrbute, to retrieve the image from.
     * The URL provided will call the Kodi controller, action getImage.
     *
     * @param object $config	    The config object representing config.ini.
     * @param mixed $type           The type of image to fetch. Either fanart or poster.
     * @param mixed $imageField     The database field to retrieve the image from.
     * @param mixed $idField        The database field to retrieve the record Id from.
     * @return string               The URL to the Kodi Controller, action getImage to retrieve the image from.
     */
    public function getImageUrl($config, $type, $imageField, $idField)
    {
        $width = $type == 'fanart' ? 800 : '350';
        $which = str_replace('Kodi', '', strtolower(get_class($this)));

        if (empty($this->{$imageField}))
        {
            return $config->application->baseUri . 'img/icons/unknown.jpg';
        }

        if ($config->imageproxy->enabled)
        {
            return $config->imageproxy->URL . (!empty($width) ? $width .  ',sc/' : null) . $this->{$imageField};
        }

        return $config->application->baseUri . 'kodi/getImage/' . $which . '/'. $type . '/' . $this->{$idField} . (!empty($width) ? '/'. $width : null);
    }

    /**
     * Returns a SimpleXMLElement created from the XML stored in the Kodi database.
     * Since the Kodi XML format stored in the database is strictly speaking not correct,
     * we need to add the XML tag and a root element before trying to create an SimpleXMLElement from it.
     *
     * @param string $kodiXml       The XML stored in the Kodi database.
     * @return \SimpleXMLElement    The Simple XML object created from the XML stored in the Kodi database.
     */
    public function getXml($kodiXml)
    {
        return new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><root>' . $kodiXml . '</root>');
    }
}