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
}