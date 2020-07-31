<?php

namespace Chell\Forms;

use Chell\Models\Devices;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * The form responsible for adding new MenuItems.
 *
 * @package Forms
 */
class SettingsMenuItemForm extends Form
{
    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $name = new Text('name');
        $name->setFilters(['striptags', 'string'])
             ->setAttributes(['class' => 'form-control', 'autocomplete' => 'off', 'id' => 'menuitem_name'])
             ->setLabel('Name')
             ->addValidator(new PresenceOf(['message' => 'Required']));

        $url = new Text('url');
        $url->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'off'])
            ->setLabel('URL')
            ->addValidator(new PresenceOf(['message' => 'Required']));

        $icon = new Text('icon');
        $icon->setFilters(['striptags', 'string'])
             ->setAttributes(['class' => 'form-control', 'autocomplete' => 'off'])
             ->setLabel('Icon');

        $device = new Select(
            'device_id' ,
            Devices::find(),
            [
                'using'         => ['id', 'name'],
                'useEmpty'      => true,
                'emptyText'     => 'None',
                'emptyValue'    => 0
            ]
        );
        $device->setLabel('Device');

        $menuId = new Hidden('menu_id');
        $menuId->setDefault(1);

        $parentId = new Hidden('parent_id');
        $parentId->setDefault(0);

        $this->add($name);
        $this->add($url);
        $this->add($icon);
        $this->add($device);
        $this->add($menuId);
        $this->add($parentId);
    }
}
