<?php

/**
 * The model responsible for all actions related to menus.
 * 
 * @package Models
 */
class Menus extends BaseModel
{
    /**
     * Sets the database relations
     * 
     * @return  void
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