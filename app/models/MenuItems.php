<?php

/**
 * The model responsible for all actions related to menu items.
 * 
 * @package Models
 */
class MenuItems extends BaseModel
{
    /**
     * Sets the database relations
     * 
     * @return  void
     */
    public function initialize()
    {
        $this->belongsTo(
            'menu_id',
            'Menus',
            'id'
        );

        $this->belongsTo(
            'device_id',
            'Devices',
            'id',
            array('alias' => 'device')
        );
    }
}