<?php

namespace Chell\Forms\Dashboard;

use Chell\Models\Devices;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

class HyperVAdminFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$hyperVAdminEnabled = new Check('hypervadmin-enabled');
		$hyperVAdminEnabled->setLabel('Enabled');
		$hyperVAdminEnabled->setAttributes([
			'checked' => $form->_config->hypervadmin->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'HyperVAdmin'
		]);

		$hyperVAdminURL = new Text('hypervadmin-url');
		$hyperVAdminURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->hypervadmin->URL);

		$hyperVAdminUsername = new Text('hypervadmin-username');
		$hyperVAdminUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->hypervadmin->username);

		$hyperVAdminPassword = new Password('hypervadmin-password');
		$hyperVAdminPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->_config->hypervadmin->password);

		$deviceOptions[0] = 'Please select';
		foreach(Devices::Find() as $device) {
			$deviceOptions[$device->id] = $device->name;
		}
		$hyperVAdminDevice = new Select('hypervadmin-device', $deviceOptions, ['fieldset' => 'end']);
		$hyperVAdminDevice->setLabel('Host');
		$hyperVAdminDevice->setDefault($form->_config->hypervadmin->device);

		$form->add($hyperVAdminEnabled);
		$form->add($hyperVAdminURL);
		$form->add($hyperVAdminUsername);
		$form->add($hyperVAdminPassword);
		$form->add($hyperVAdminDevice);
	}

    public function setPostData(&$config, $data)
    {
        $config->hypervadmin->enabled = isset($data['hypervadmin-enabled']) && $data['hypervadmin-enabled'] == 'on' ? '1' : '0';
        $config->hypervadmin->URL = $data['hypervadmin-url'];
        $config->hypervadmin->username = $data['hypervadmin-username'];
        $config->hypervadmin->password = $data['hypervadmin-password'];
        $config->hypervadmin->device = $data['hypervadmin-device'];
    }
}