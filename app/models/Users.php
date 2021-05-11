<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to Users.
 *
 * @package Models
 */
class Users extends Model
{
    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->hasManyToMany(
            'id',
            'Chell\Models\MenuItemsUsers',
            'user_id',
            'menu_item_id',
            'Chell\Models\MenuItems',
            'id',
            ['alias' => 'menuitems']
        );
    }

    public static function createNewMenuItemLinks($userIds, $itemId)
    {
        $users = Users::find([
            'id IN ({userids:array})',
            'bind' => ['userids' => $userIds]
        ]);

        foreach ($users as $user)
        {
            $menuItems = $user->getMenuitems([
                'menu_item_id = {id:int}',
                'bind' => ['id' => $itemId]
            ]);

            if ($menuItems->count() == 0)
            {
                (new MenuItemsUsers([
                    'user_id' => $user->id,
                    'menu_item_id' => $itemId
                ]))->save();
            }
        }
    }
}