<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;

class SettingsGeneralForm extends Form
{
    private $_config;

    public function initialize($config)
    {
        $this->_config = $config;
        $this->_action = 'general';

        $title = new Text('title');
        $title->setLabel('Title');
        $title->setFilters(array('striptags', 'string'));
        $title->setAttributes(array('class' => 'form-control'));
        $title->setDefault($config->application->title);
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
            $this->config->application->title = $data['title'];
        }

        return $valid;
    }
}
