<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * The form responsible for the general settings.
 * 
 * @package Forms
 */
class SettingsGeneralForm extends Form
{
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
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $this->_action = 'general';

        $title = new Text('title');
        $title->setLabel('Title');
        $title->setFilters(array('striptags', 'string'));
        $title->setAttributes(array('class' => 'form-control'));
        $title->setDefault($this->_config->application->title);
        $title->addValidators(array(new PresenceOf(array())));

        $cryptKey = new Text('cryptkey');
        $cryptKey->setLabel('Cryptkey');
        $cryptKey->setFilters(array('striptags', 'string'));
        $cryptKey->setAttributes(array('class' => 'form-control'));
        $cryptKey->setDefault($this->_config->application->phalconCryptKey);
        $cryptKey->addValidators(array(new PresenceOf(array())));

        $bgcolor = new Select(
            'bgcolor',
            array('blackbg' => 'Black', 'whitebg' => 'White'),
            array('useEmpty' => false)
        );
        $bgcolor->setLabel('Background color');
        $bgcolor->setDefault($this->_config->application->background);

        $debug = new Check('debug');
        $debug->setLabel('debug');
        $debug->setAttributes(array('checked' => $this->_config->application->debug == '1' ? 'checked' : null));

        $this->add($title);
        $this->add($bgcolor);
        $this->add($cryptKey);
        $this->add($debug);
    }

    /**
     * Check if form is valid. If so set the values to the config array.
     * 
     * @param array $data   The form data posted.
     * @return bool         Whether or not form is valid.
     */
    public function IsValid($data = null, $entity = null)
    {
        $valid = parent::IsValid($data, $entity);
        
        if($valid)
        {
            $this->_config->application->title = $data['title'];
            $this->_config->application->phalconCryptKey = $data['cryptkey'];
            $this->_config->application->background = $data['bgcolor'];
            $this->_config->application->debug = $data['debug'] ? '1' : '0';
        }

        return $valid;
    }
}
