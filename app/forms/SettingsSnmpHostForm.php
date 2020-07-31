<?php

namespace Chell\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * The form responsible for adding new SNMP hosts.
 *
 * @package Forms
 */
class SettingsSnmpHostForm extends Form
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
            ->addValidator(new PresenceOf(['message' => 'Required']));

        $ip = new Text('ip');
        $ip->setLabel('IP')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->addValidator(new PresenceOf(['message' => 'Required']));

        $community = new Text('community');
        $community->setLabel('Community')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->addValidator(new PresenceOf(['message' => 'Required']));

        $version = new Select(
            'version',
            ['1' => '1', '2C' => '2C', '3' => '3'],
            ['useEmpty' => false]
        );
        $version->setLabel('SNMP Version');

        $this->add($name);
        $this->add($ip);
        $this->add($community);
        $this->add($version);
    }
}