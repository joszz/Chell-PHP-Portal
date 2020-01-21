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
    public function getImageUrl($config, $type, $imageProperty, $idProperty)
    {
        $width =$type == 'fanart' ? 800 : '';
        $which = str_replace('Kodi', '', strtolower(get_class($this)));

        if (empty($this->{$imageProperty}))
        {
            return $config->application->baseUri . 'img/icons/unknown.jpg';
        }

        if($config->imageproxy->enabled)
        {
            return $config->imageproxy->URL . (!empty($width) ? $width .  '/' : null) . $this->{$imageProperty};
        }

        return $config->application->baseUri . 'kodi/getImage/' . $which . '/'. $type . '/' . $this->{$idProperty} . (!empty($width) ? '/'. $width : null);
    }

    public function getXml($kodiXml)
    {
        return new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><root>' . $kodiXml . '</root>');
    }
}