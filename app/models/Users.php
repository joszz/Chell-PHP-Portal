<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;
use Chell\Models\MenuItems;
use Chell\Models\MenuItemsUsers;

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
            MenuItemsUsers::class,
            'user_id',
            'menu_item_id',
            MenuItems::class,
            'id',
            ['alias' => 'menuitems']
        );
    }
}