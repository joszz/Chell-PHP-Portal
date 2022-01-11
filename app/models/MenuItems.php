<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Url;

/**
 * The model responsible for all actions related to menu items.
 *
 * @package Models
 * @suppress PHP2414
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
     * @return string           The path to the icon.
     */
    public function getIconFilePath() : string
    {
        return APP_PATH . 'img/icons/menu/' . $this->id . '.png';
    }

    /**
     * Given a full filepath to an image, resizes the image and outputs it as PNG.
     *
     * @param string $filename  The full filename to the image.
     */
    public function resizeIcon(string $filename)
    {
        list($width, $height) = getimagesize($filename);
        $source = imagecreatefromstring(file_get_contents($filename));
        $thumb = imagecreatetruecolor(16, 16);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true );
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, 16, 16, $width, $height);
        imagepng($thumb, $filename);
    }

    /**
     * Adds and deletes MenuItems associated to the user.
     *
     * @param array $userIds    The Ids of users to add or remove the MenuItem link for.
     */
    public function handlePost(array $userIds)
    {
        $this->addToUsers($userIds);
        $this->deleteFromUsers($userIds);
    }

    /**
     * Given an array of user Ids, add a menu_items_user record linking this MenuItem to the users.
     *
     * @param array $userIds    The Ids of users to link to this MenuItem.
     */
    private function addToUsers(array $userIds)
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

    /**
     * Given an array of user Ids, removes menu_items_user records linking this MenuItem that are not equal to provided user Ids.
     *
     * @param array $userIds    The Ids of users to remove the link to this MenuItem for.
     * @return bool             A boolean indicating success or failure.
     */
    private function deleteFromUsers(array $userIds) : bool
    {
        return MenuItemsUsers::find([
            'user_id NOT IN ({userids:array}) AND menu_item_id = {menuitemid:int}',
            'bind' => [
                'userids' => $userIds,
                'menuitemid' => $this->id
            ]
        ])->delete();
    }
}