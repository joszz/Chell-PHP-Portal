<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * The form responsible for updating existing Users.
 *
 * @package Forms
 */
class SettingsUsersForm extends Form
{
    /**
     * The configuration object containing all the info from config.ini.
     * @var array
     */
    private $_config;

    /**
     * Set the config array (config.ini contents) to private variable.
     *
     * @param array $config     The config array.
     */
    public function __construct($config)
    {
        $this->_config = $config;
        parent::__construct();
    }

    /**
     * Loops over all users and sets fields for each user
	 *
	 * @param array $users	All users currently stored in the database.
     */
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