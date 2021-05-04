<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to menu items.
 *
 * @package Models
 */
class MenuItems extends Model
{
    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->belongsTo(
            'menu_id',
            'Chell\Models\Menus',
            'id'
        );

        $this->belongsTo(
            'device_id',
            'Chell\Models\Devices',
            'id',
            ['alias' => 'device']
        );
    }

    /**
     * Returns the absolute path to the menuitem's icon,
     * 
     * @return string   The path to the icon
     */
    public function getIconFilePath($baseUri) : string
    {
        return $baseUri . 'img/icons/menu/' . $this->id . '.png';
    }

    /**
     * Given a full filepath to an image, resizes the image and outputs it as PNG.
     * 
     * @param string $filename  The full filename to the image.
     */
    public function resizeIcon($filename)
    {
        list($width, $height) = getimagesize($filename);
        $source = imagecreatefromstring(file_get_contents($filename));
        $thumb = imagecreatetruecolor(16, 16);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, 16, 16, $width, $height);
        imagepng($thumb, $filename);
    }
}