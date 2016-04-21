<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

class SettingsDevicesForm extends Form
{
    public function initialize($devices)
    {
        $this->_action = 'devices';

        foreach($devices as $device)
        {
            $name = new Text('device[' . $device->id . '][name]');
            $name->setLabel('Devicename');
            $name->setFilters(array('striptags', 'string'));
            $name->setAttributes(array('class' => 'form-control'));
            $name->setDefault($device->name);

            $ip = new Text('device[' . $device->id . '][ip]');
            $ip->setLabel('IP');
            $ip->setFilters(array('striptags', 'string'));
            $ip->setAttributes(array('class' => 'form-control'));
            $ip->setDefault($device->ip);

            $mac = new Text('device[' . $device->id . '][mac]');
            $mac->setLabel('MAC');
            $mac->setFilters(array('striptags', 'string'));
            $mac->setAttributes(array('class' => 'form-control'));
            $mac->setDefault($device->mac);

            $webtemp = new Text('device[' . $device->id . '][webtemp]');
            $webtemp->setLabel('Webtemp path');
            $webtemp->setFilters(array('striptags', 'string'));
            $webtemp->setAttributes(array('class' => 'form-control'));
            $webtemp->setDefault($device->webtemp);

            $shutdownMethod = new Select(
                'device[' . $device->id . '][shutdown_method]',
                array('none' => 'None', 'rpc' => 'RPC'),
                array(
                    'useEmpty'      => false,
                )
            );
            $shutdownMethod->setDefault($device->shutdown_method);

            $id = new Hidden('device[' . $device->id . '][id]');
            $id->setDefault($device->id);
            
            $this->add($name);
            $this->add($ip);
            $this->add($mac);
            $this->add($webtemp);
            $this->add($shutdownMethod);
            $this->add($id);
        }
    }
}