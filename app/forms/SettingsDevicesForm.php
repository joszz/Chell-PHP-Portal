<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;


/**
 * The form responsible for updating existing devices.
 *
 * @package Forms
 */
class SettingsDevicesForm extends Form
{
    /**
     * Initializes the form, calls addFields for each device in the database.
	 *
	 * @param Array $devices	All the currently available devices.
     */
    public function initialize($devices)
    {
        $this->_action = 'devices';

        if(count($devices) > 1)
        {
            foreach($devices as $device)
            {
                $this->addFields($device);
            }
        }
    }

    /**
     * Add all fields to the form and set form specific attributes.
     *
     * @param Object $device    The device to add the fields for.
     */
    private function addFIelds($device)
    {
        $name = new Text('devices[' . $device->id . '][name]');
        $name->setLabel('Devicename');
        $name->setFilters(array('striptags', 'string'));
        $name->setAttributes(array('class' => 'form-control'));
        $name->setDefault($device->name);
        $name->addValidators(array(new PresenceOf(array())));

        $ip = new Text('devices[' . $device->id . '][ip]');
        $ip->setLabel('IP');
        $ip->setFilters(array('striptags', 'string'));
        $ip->setAttributes(array('class' => 'form-control'));
        $ip->setDefault($device->ip);
        $ip->addValidator(new Regex(array('pattern' => '/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/')));

        $mac = new Text('devices[' . $device->id . '][mac]');
        $mac->setLabel('MAC');
        $mac->setFilters(array('striptags', 'string'));
        $mac->setAttributes(array('class' => 'form-control'));
        $mac->setDefault($device->mac);
        $mac->addValidator(new Regex(array('pattern' => '/^([0-9A-Fa-f]{2}[:]){5}([0-9A-Fa-f]{2})$/')));

        $webtemp = new Text('devices[' . $device->id . '][webtemp]');
        $webtemp->setLabel('Webtemp path');
        $webtemp->setFilters(array('striptags', 'string'));
        $webtemp->setAttributes(array('class' => 'form-control'));
        $webtemp->setDefault($device->webtemp);

        $shutdownMethod = new Select(
            'devices[' . $device->id . '][shutdown_method]',
            array('none' => 'None', 'rpc' => 'RPC'),
            array(
                'useEmpty'      => false,
            )
        );
        $shutdownMethod->setDefault($device->shutdown_method);

        $showDasboard = new Check('devices[' . $device->id . '][show_on_dashboard]');
        $showDasboard->setLabel('Show on dashboard');
        $showDasboard->setFilters(array('striptags', 'int'));
        $showDasboard->setAttributes(array('class' => 'form-control'));
        $showDasboard->setDefault($device->show_on_dashboard);

        $id = new Hidden('devices[' . $device->id . '][id]');
        $id->setDefault($device->id);

        $this->add($name);
        $this->add($ip);
        $this->add($mac);
        $this->add($webtemp);
        $this->add($shutdownMethod);
        $this->add($showDasboard);
        $this->add($id);
    }

    /**
     * Loop through all posted devices and check if they're valid.
     *
     * @param   array     $data     The form data posted.
     * @param   object    $entity   The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
    public function IsValid($data = null, $entity = null)
    {
        $devices = array();

        foreach($data['devices'] as $id => $device)
        {
            $newDevice = new Devices();
            $newDevice->id = $id;

            foreach($device as $key => $value)
            {
                $newDevice->{$key} = $value;
            }

            $newDevice->validate();
            $devices[] = $newDevice;
        }

        return $devices;
    }
}