<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

class LoginForm extends Form
{
    public function initialize()
    {
        $this->_action = 'session/login';

        $username = new Text('username');
        $username->setLabel('Username');
        $username->setFilters(array('striptags', 'string'));
        $username->setAttributes(array('placeholder' => 'Username', 'class' => 'form-control'));

        $password = new Password('password');
        $password->setLabel('Password');
        $password->setFilters(array('striptags', 'string'));
        $password->setAttributes(array('placeholder' => 'Password', 'class' => 'form-control'));

        $rememberme = new Check('rememberme');
        $rememberme->setLabel('Remember me');

        $this->add($username);
        $this->add($password);
        $this->add($rememberme);
    }
}