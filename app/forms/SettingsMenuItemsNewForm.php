<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

class SettingsMenuItemsNewForm extends Form
{
    public function initialize()
    {
        $this->_action = 'menuitem_add';

        $name = new Text('name');
        $name->setFilters(array('striptags', 'string'));
        $name->setAttributes(array('class' => 'form-control'));
        $name->setLabel('Name');

        $url = new Text('url');
        $url->setFilters(array('striptags', 'string'));
        $url->setAttributes(array('class' => 'form-control'));
        $url->setLabel('URL');

        $icon = new Text('icon');
        $icon->setFilters(array('striptags', 'string'));
        $icon->setAttributes(array('class' => 'form-control'));
        $icon->setLabel('Icon');

        $device = new Select(
            'device_id' ,
            Devices::find(),
            array(
                'using'         => array('id', 'name'),
                'useEmpty'      => true,
                'emptyText'     => 'None',
                'emptyValue'    => 0
            )
        );
        $device->setLabel('Device');

        $menuId = new Hidden('menu_id');
        $menuId->setDefault(1);

        $this->add($name);
        $this->add($url);
        $this->add($icon);
        $this->add($device);
        $this->add($menuId);
    }
}
