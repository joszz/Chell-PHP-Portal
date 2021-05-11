<?php

namespace Chell\Forms;

use Chell\Models\Devices;
use Chell\Models\Users;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Url as UrlValidator;

/**
 * The form responsible for adding new MenuItems.
 *
 * @package Forms
 */
class SettingsMenuItemForm extends SettingsBaseForm
{
    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize($entity = null)
    {
        $this->setAttributes(new \Phalcon\Html\Attributes(['enctype' => 'enctype="multipart/form-data"']));
        $name = new Text('name');
        $name->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'off', 'id' => 'menuitem_name'])
            ->setLabel('Name')
            ->addValidator(new PresenceOf(['message' => $this->translator->validation['required']]));

        $url = new Text('url');
        $url->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'off'])
            ->setLabel('URL')
            ->addValidators([
                new PresenceOf(['message' => $this->translator->validation['required']]),
                new UrlValidator(['message' => $this->translator->validation['url']])
            ]);

        $icon = new File('icon');
        $icon->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'accept' => '.jpg,.png,.gif,.bmp'])
            ->setLabel('Icon')
            ->setDefault(isset($entity) ? $entity->id . '.png' : '')
            ->setUserOptions(['buttons' => ['menuitem_icon']]);

        $device = new Select(
            'device_id' ,
            Devices::find(),
            [
                'using'         => ['id', 'name'],
                'useEmpty'      => true,
                'emptyText'     => 'None',
                'emptyValue'    => null
            ]
        );
        $device->setLabel('Device');

        $allUsers = Users::find();
        $users = new Select(
            'user_id[]' ,
            $allUsers,
            [
                'using'     => ['id', 'username'],
				'multiple'  => 'multiple'
            ],
        );
        $users->setLabel('Users')->setDefault($this->getSelectedUsers($entity, $allUsers));

        $this->add($name);
        $this->add($url);
        $this->add($icon);
        $this->add($device);
        $this->add($users);
    }

    /**
     * Given the MenuItem entity, get the selected users.
     *
     * @param \Chell\Models\MenuItems $entity   The MenuItem to get the selected users for.
     * @return array    An array of User Ids.
     */
    private function getSelectedUsers($entity, $allUsers)
    {
        $selectedUsers = [];

        if(isset($entity))
        {
            $users = $entity->getUsers();
            foreach ($users as $user)
            {
                $selectedUsers[] = $user->id;
            }
        }

        if (!count($selectedUsers) && $allUsers->count() === 1)
        {
            $selectedUsers = [$allUsers[0]->id];
        }

        return $selectedUsers;
    }
}
