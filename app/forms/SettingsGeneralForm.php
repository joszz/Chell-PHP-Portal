<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

class SettingsGeneralForm extends Form
{
    private $_config;

    public function __construct($config)
    {
        $this->_config = $config;
        parent::__construct();
    }

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

    public function IsValid($data)
    {
        $valid = parent::IsValid($data);
        
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
