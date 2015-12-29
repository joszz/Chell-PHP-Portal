<?php

class MenuItems extends BaseModel
{
    public $id;
    public $menuId;
    public $parentId;
    public $name;
    public $url;

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