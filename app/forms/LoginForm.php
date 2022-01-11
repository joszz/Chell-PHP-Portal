<?php

namespace Chell\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Check;

/**
 * The from responsible for handling login to the application.
 *
 * @package Forms
 */
class LoginForm extends Form
{
    /**
     * Set the config array (config.ini contents) to private variable.
     * Set bool if login already failed on last request.
     *
     * @param bool  $_loginFailed    Whether login failed on last POST.
     */
    public function __construct(private bool $_loginFailed)
    {
        parent::__construct();
    }

    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $this->setAction( $this->url->get('session/login'));

        $username = new Text('username');
        $username->setLabel('Username');
        $username->setFilters(['striptags', 'string']);
        $username->setAttributes(['placeholder' => 'Username', 'class' => 'form-control' . ($this->_loginFailed ? ' has-error' : null)]);

        $password = new Password('password');
        $password->setLabel('Password');
        $password->setFilters(['striptags', 'string']);
        $password->setAttributes(['placeholder' => 'Password', 'class' => 'form-control' . ($this->_loginFailed ? ' has-error' : null)]);

        $rememberme = new Check('rememberme');
        $rememberme->setLabel('Remember me');
        $rememberme->setDefault('checked');
        $rememberme->setAttributes([
            'checked' => 'checked',
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small'
        ]);

        $this->add($username);
        $this->add($password);
        $this->add($rememberme);
    }
}