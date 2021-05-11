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
        $this->hasManyToMany(
            'id',
            'Chell\Models\MenuItemsUsers',
            'menu_item_id',
            'user_id',
            'Chell\Models\Users',
            'id',
            ['alias' => 'users']
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
     * @param string $baseUri   The base URI to start the icon file path with.
     * @return string           The path to the icon.
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
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, 16, 16, $width, $height);
        imagepng($thumb, $filename);
    }

    public function handlePost($userIds){
        $this->addToUsers($userIds);
        $this->deleteFromUsers($userIds);
    }

    private function addToUsers($userIds)
    {
        $users = Users::find([
            'id IN ({userids:array})',
            'bind' => ['userids' => $userIds]
        ]);

        foreach ($users as $user)
        {
            $menuItems = $user->getMenuitems([
                'menu_item_id = {id:int}',
                'bind' => ['id' => $this->id]
            ]);

            if ($menuItems->count() == 0)
            {
                (new MenuItemsUsers([
                    'user_id' => $user->id,
                    'menu_item_id' => $this->id
                ]))->save();
            }
        }
    }

    private function deleteFromUsers($userIds)
    {
        MenuItemsUsers::find([
            'user_id NOT IN ({userids:array}) AND menu_item_id = {menuitemid:int}',
            'bind' => [
                'userids' => $userIds,
                'menuitemid' => $this->id
            ]
        ])->delete();
    }
}