<?php

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to menus.
 *
 * @package Models
 */
class Menus extends Model
{
    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->hasMany(
            'id',
            'MenuItems',
            'menu_id',
            array('alias' => 'items')
        );
    }
}