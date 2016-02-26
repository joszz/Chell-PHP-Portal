<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;

class SettingsForm extends Form
{
    public function initialize($config)
    {
        $title = new Text('title');
        $title->setLabel('Title');
        $title->setFilters(array('striptags', 'string'));
        $title->setAttributes(array('class' => 'form-control'));
        $title->setDefault($config->application->title);


        $this->add($title);
    }
}
