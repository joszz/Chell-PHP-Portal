<?php

namespace Chell\Models\Kodi;

use SimpleXMLElement;
use Chell\Models\BaseModel;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

/**
 * The model responsible for all Kodi movies.
 *
 * @package Models\Kodi
 * @suppress PHP2414
 */
class KodiBase extends BaseModel
{
    /**
     * Initializes DB connections for Kodi databases.
     */
    public function initialize()
    {
        $config = $this->_settings->kodi;
        $this->di->set('dbKodiMusic', function() use ($config) {
            return new DbAdapter([
                'host'     => $this->sett->database->host,
                'username' => $config->dbusername,
                'password' => $config->dbpassword,
                'dbname'   => $config->dbmusic,
                'charset'  => 'utf8'
            ]);
        });

        $this->di->set('dbKodiVideo', function() use ($config) {
            return new DbAdapter([
                'host'     => $config->database->dbhost,
                'username' => $config->database->dbusername,
                'password' => $config->database->dbpassword,
                'dbname'   => $config->database->dbvideo,
                'charset'  => 'utf8'
            ]);
        });

        parent::initialize();
    }
    /**
     * Retrieves the image URL, used in the src attrbute, to retrieve the image from.
     * The URL provided will call the Kodi controller, action getImage.
     *
     * @param string $type           The type of image to fetch. Either fanart or poster.
     * @param string $imageField     The database field to retrieve the image from.
     * @param string $idField        The database field to retrieve the record Id from.
     * @return string               The URL to the Kodi Controller, action getImage to retrieve the image from.
     */
    public function getImageUrl(string $type, string $imageField, string $idField) : string
    {
        $width = $type == 'fanart' ? 800 : '350';
        $which = str_replace('Kodi', '', strtolower($this::class));

        if (empty($this->{$imageField}))
        {
            return $this->url->get('img/icons/unknown.jpg');
        }

        if ($this->_settings->imageproxy->enabled)
        {
            return $this->_settings->imageproxy->url . (!empty($width) ? $width .  ',sc/' : null) . $this->{$imageField};
        }

        return $this->url->get('kodi/getImage/' . $which . '/'. $type . '/' . $this->{$idField} . (!empty($width) ? '/'. $width : null));
    }

    /**
     * Returns a SimpleXMLElement created from the XML stored in the Kodi database.
     * Since the Kodi XML format stored in the database is strictly speaking not correct,
     * we need to add the XML tag and a root element before trying to create an SimpleXMLElement from it.
     *
     * @param string $kodiXml       The XML stored in the Kodi database.
     * @return SimpleXMLElement    The Simple XML object created from the XML stored in the Kodi database.
     */
    public function getXml(string $kodiXml) : SimpleXMLElement
    {
        return new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><root>' . $kodiXml . '</root>');
    }
}