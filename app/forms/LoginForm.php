<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

class LoginForm extends Form
{
    private $_loginFailed = false;

    public function __construct($config, $loginFailed)
    {
        $this->_config = $config;
        $this->_loginFailed = $loginFailed;

        parent::__construct();
    }

    public function initialize()
    {
        $this->_action = $this->config->application->baseUri . 'session/login';

        $username = new Text('username');
        $username->setLabel('Username');
        $username->setFilters(array('striptags', 'string'));
        $username->setAttributes(array('placeholder' => 'Username', 'class' => 'form-control' . ($this->_loginFailed ? ' has-error' : null)));

        $password = new Password('password');
        $password->setLabel('Password');
        $password->setFilters(array('striptags', 'string'));
        $password->setAttributes(array('placeholder' => 'Password', 'class' => 'form-control' . ($this->_loginFailed ? ' has-error' : null)));

        $rememberme = new Check('rememberme');
        $rememberme->setLabel('Remember me');

        $this->add($username);
        $this->add($password);
        $this->add($rememberme);
    }
}