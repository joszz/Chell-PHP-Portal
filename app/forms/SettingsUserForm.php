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
     * @param array $users All users currently stored in the database.
     */
    public function initialize($user)
    {
        $username = new Text('username');
        $username->setLabel('Username');
        $username->setFilters(['striptags', 'string']);
        $username->setAttributes(['class' => 'form-control', 'autocomplete' => 'off']);
        $username->addValidators([new PresenceOf([])]);

        if (isset($user->username))
        {
            $username->setDefault($user->username);
        }

        $email = new Email('email');
        $email->setLabel('Email');
        $email->setFilters(['striptags', 'string']);
        $email->setAttributes(['class' => 'form-control', 'autocomplete' => 'off']);

        $password = new Password('password');
        $password->setLabel('Password');
        $password->setFilters(['striptags', 'string']);
        $password->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password']);

        $passwordAgain = new Password('password_again');
        $passwordAgain->setLabel('Password again');
        $passwordAgain->setFilters(['striptags', 'string']);
        $passwordAgain->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password']);

        $this->add($username);
        $this->add($email);
        $this->add($password);
        $this->add($passwordAgain);
    }
}