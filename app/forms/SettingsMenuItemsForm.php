<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

/**
 * The form responsible for updating existing MenuItems.
 * 
 * @package Forms
 */
class SettingsMenuItemsForm extends Form
{
    /**
     * Loops over all menuitems and sets fields for each menuitem
	 * 
	 * @param array $menuitems	All menuitems currently stored in the database.
     */
    public function initialize($menuitems)
    {
        $this->_action = 'menu';

        foreach($menuitems as $menuItem)
        {
            $name = new Text('menuitem[' . $menuItem->id . '][name]');
            $name->setFilters(array('striptags', 'string'));
            $name->setAttributes(array('class' => 'form-control'));
            $name->setDefault($menuItem->name);

            $url = new Text('menuitem[' . $menuItem->id . '][url]');
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

            $this->add($name);
            $this->add($url);
            $this->add($icon);
            $this->add($device);
            $this->add($id);
        }
    }
}
