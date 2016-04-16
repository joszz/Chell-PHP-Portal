<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

class SettingsMenuForm extends Form
{
    public function initialize()
    {
        $this->_action = 'menu';

        foreach($this->view->menu->getItems(array('order' => 'name ASC')) as $menuItem)
        {
            $url = new Text('menuitem[' . $menuItem->id . '][url]');
            $url->setLabel($menuItem->name);
            $url->setFilters(array('striptags', 'string'));
            $url->setAttributes(array('class' => 'form-control'));
            $url->setDefault($menuItem->url);

            $icon = new Text('menuitem[' . $menuItem->id . '][icon]');
            $icon->setFilters(array('striptags', 'string'));
            $icon->setAttributes(array('class' => 'form-control'));
            $icon->setDefault($menuItem->icon);

            $device = new Select(
                'menuitem[' . $menuItem->id . '][device]',
                Devices::find(),
                array(
                    'using'         => array('id', 'name'),
                    'useEmpty'      => true,
                    'emptyText'     => 'None',
                    'emptyValue'    => 0
                )
            );
            $device->setDefault($menuItem->device_id);

            $id = new Hidden('menuitem[' . $menuItem->id . '][id]');
            $id->setDefault($menuItem->id);

            $this->add($url);
            $this->add($icon);
            $this->add($device);
            $this->add($id);
        }
    }
}
