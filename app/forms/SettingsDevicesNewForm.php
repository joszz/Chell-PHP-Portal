<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

class SettingsDevicesNewForm extends Form
{
    public function initialize()
    {
        $this->_action = 'devices_add';

        $name = new Text('name');
        $name->setLabel('Devicename');
        $name->setFilters(array('striptags', 'string'));
        $name->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'));

        $ip = new Text('ip');
        $ip->setLabel('IP');
        $ip->setFilters(array('striptags', 'string'));
        $ip->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'));

        $mac = new Text('mac');
        $mac->setLabel('MAC');
        $mac->setFilters(array('striptags', 'string'));
        $mac->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'));

        $webtemp = new Text('webtemp');
        $webtemp->setLabel('Webtemp path');
        $webtemp->setFilters(array('striptags', 'string'));
        $webtemp->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'));

        $shutdownMethod = new Select(
            'shutdown_method',
            array('none' => 'None', 'rpc' => 'RPC'),
            array(
                'useEmpty'      => false
            )
        );
        $shutdownMethod->setLabel('Shutdown method');
            
        $this->add($name);
        $this->add($ip);
        $this->add($mac);
        $this->add($webtemp);
        $this->add($shutdownMethod);
    }
}