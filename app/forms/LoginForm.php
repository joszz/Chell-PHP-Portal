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
     * Whether login failed on last POST.
     * @var bool
     */
    private $_loginFailed = false;

    /**
     * Set the config array (config.ini contents) to private variable.
     * Set bool if login already failed on last request.
     *
     * @param array $config         The config array.
     * @param bool  $loginFailed    Whether login failed on last POST.
     */
    public function __construct($config, $loginFailed)
    {
        $this->_config = $config;
        $this->_loginFailed = $loginFailed;

        parent::__construct();
    }

    /**
     * Add all fields to the form and set form specific attributes.
     */
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
        $rememberme->setAttributes(array(
            'checked' => true,
            'data-toggle' => 'toggle',
            'data-on' => 'Remember me',
            'data-onstyle' => 'success',
            'data-off' => 'Don\'t remember',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'data-width' => '115px'
        ));

        $this->add($username);
        $this->add($password);
        $this->add($rememberme);
    }
}