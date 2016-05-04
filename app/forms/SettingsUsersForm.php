<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;

class SettingsUsersForm
{
    public function initialize($users)
    {
        foreach($users as $user)
        {
            $username = new Text('user[' . $user->id . '][username]');
            $username->setLabel('Check device state timeouts');
            $username->setFilters(array('striptags', 'string'));
            $username->setAttributes(array('class' => 'form-control'));
            $username->setDefault($user->username);

            $this->add($username);
        }
    }
}