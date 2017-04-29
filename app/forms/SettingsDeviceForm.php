<?php

namespace Chell\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * The form responsible for adding new devices.
 *
 * @package Forms
 */
class SettingsDeviceForm extends Form
{
    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $name = new Text('name');
        $name->setLabel('Name')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'))
            ->addValidator(new PresenceOf(["message" => "Required"]));

        $ip = new Text('ip');
        $ip->setLabel('IP')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'))
            ->addValidator(new PresenceOf(["message" => "Required"]));

        $mac = new Text('mac');
        $mac->setLabel('MAC')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'));

        $webtemp = new Text('webtemp');
        $webtemp->setLabel('Webtemp path')
                ->setFilters(array('striptags', 'string'))
                ->setAttributes(array('class' => 'form-control', 'autocomplete' => 'off'));

        $shutdownMethod = new Select(
            'shutdown_method',
            array('none' => 'None', 'rpc' => 'RPC'),
            array(
                'useEmpty'      => false
            )
        );
        $shutdownMethod->setLabel('Shutdown method');

        $showDasboard = new Check('show_on_dashboard', ['value' => '1']);
        $showDasboard->setLabel('Show on dashboard')
                     ->setAttributes(array(
                        'data-toggle' => 'toggle',
                        'data-onstyle' => 'success',
                        'data-offstyle' => 'danger',
                        'data-size' => 'small'
        ));

        $this->add($name);
        $this->add($ip);
        $this->add($mac);
        $this->add($webtemp);
        $this->add($shutdownMethod);
        $this->add($showDasboard);
    }
}