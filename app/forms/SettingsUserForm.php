<?php

namespace Chell\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Email;

use Phalcon\Validation\Validator\PresenceOf;

/**
 * The form responsible for updating existing Users.
 *
 * @package Forms
 */
class SettingsUserForm extends Form
{
    /**
     * Loops over all users and sets fields for each user
     *
     * @param array $users	All users currently stored in the database.
     */
    public function initialize($user)
    {
        $username = new Text('username');
        $username->setLabel('Username');
        $username->setFilters(array('striptags', 'string'));
        $username->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'));
        $username->addValidators(array(new PresenceOf(array())));

        if(isset($user->username)){
            $username->setDefault($user->username);
        }

        $email = new Email('email');
        $email->setLabel('Email');
        $email->setFilters(array('striptags', 'string'));
        $email->setAttributes(array('class' => 'form-control', 'autocomplete' => 'new-password'));

        $password = new Password('password');
        $password->setLabel('Password');
        $password->setFilters(array('striptags', 'string'));
        $password->setAttributes(array('class' => 'form-control', 'autocomplete' => 'new-password'));

        $passwordAgain = new Password('password_again');
        $passwordAgain->setLabel('Password again');
        $passwordAgain->setFilters(array('striptags', 'string'));
        $passwordAgain->setAttributes(array('class' => 'form-control', 'autocomplete' => 'new-password'));

        $this->add($username);
        $this->add($email);
        $this->add($password);
        $this->add($passwordAgain);
    }
}