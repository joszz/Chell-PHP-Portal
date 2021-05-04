<?php

namespace Chell\Models\Kodi;

use Chell\Models\BaseModel;

/**
 * The model responsible for all Kodi movies.
 *
 * @package Models\Kodi
 */
class KodiBase extends BaseModel
{
    /**
     * Retrieves the image URL, used in the src attrbute, to retrieve the image from.
     * The URL provided will call the Kodi controller, action getImage.
     *
     * @param mixed $type           The type of image to fetch. Either fanart or poster.
     * @param mixed $imageField     The database field to retrieve the image from.
     * @param mixed $idField        The database field to retrieve the record Id from.
     * @return string               The URL to the Kodi Controller, action getImage to retrieve the image from.
     */
    public function getImageUrl($type, $imageField, $idField) : string
    {
        $width = $type == 'fanart' ? 800 : '350';
        $which = str_replace('Kodi', '', strtolower(get_class($this)));

        if (empty($this->{$imageField}))
        {
            return $this->_config->application->baseUri . 'img/icons/unknown.jpg';
        }

        if ($this->_config->imageproxy->enabled)
        {
            return $this->_config->imageproxy->URL . (!empty($width) ? $width .  ',sc/' : null) . $this->{$imageField};
        }

        return $this->_config->application->baseUri . 'kodi/getImage/' . $which . '/'. $type . '/' . $this->{$idField} . (!empty($width) ? '/'. $width : null);
    }

    /**
     * Returns a SimpleXMLElement created from the XML stored in the Kodi database.
     * Since the Kodi XML format stored in the database is strictly speaking not correct,
     * we need to add the XML tag and a root element before trying to create an SimpleXMLElement from it.
     *
     * @param string $kodiXml       The XML stored in the Kodi database.
     * @return \SimpleXMLElement    The Simple XML object created from the XML stored in the Kodi database.
     */
    public function getXml($kodiXml) : \SimpleXMLElement
    {
        return new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><root>' . $kodiXml . '</root>');
    }
}