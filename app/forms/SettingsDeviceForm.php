<?php

namespace Chell\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
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
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->addValidator(new PresenceOf(["message" => "Required"]));

        $ip = new Text('ip');
        $ip->setLabel('IP')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->addValidator(new PresenceOf(["message" => "Required"]));

        $mac = new Text('mac');
        $mac->setLabel('MAC')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control']);

        $shutdownUser = new Text('shutdown_user');
        $shutdownUser->setLabel('Shutdown user')
                ->setFilters(['striptags', 'string'])
                ->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-user']);

        $shutdownPassword = new Password('shutdown_password');
        $shutdownPassword->setLabel('Shutdown password')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password']);

        $shutdownMethod = new Select(
            'shutdown_method',
            ['none' => 'None', 'rpc' => 'RPC'],
            ['useEmpty' => false]
        );
        $shutdownMethod->setLabel('Shutdown method');

        $showDasboard = new Check('show_on_dashboard', ['value' => '1']);
        $showDasboard->setLabel('Show on dashboard')
                     ->setAttributes([
                        'data-toggle' => 'toggle',
                        'data-onstyle' => 'success',
                        'data-offstyle' => 'danger',
                        'data-size' => 'small'
        ]);

        $this->add($name);
        $this->add($ip);
        $this->add($mac);
        $this->add($shutdownUser);
        $this->add($shutdownPassword);
        $this->add($shutdownMethod);
        $this->add($showDasboard);
    }
}