<?php

class Menus extends BaseModel
{
    public $id;
    public $name;

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