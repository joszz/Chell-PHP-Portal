<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
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
        $title->addValidators(array(
            new PresenceOf(array(
                'Message' => 'Title is required'
            ))
        ));

        $this->add($title);
    }

    public function IsValid($data)
    {
        $valid = parent::IsValid($data);

        if($valid)
        {
            $this->_config->application->title = $data['title'];
        }

        return $valid;
    }
}
