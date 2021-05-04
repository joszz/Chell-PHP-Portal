<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Models\Devices;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

class HyperVAdminFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$hyperVAdminEnabled = new Check('hypervadmin-enabled');
		$hyperVAdminEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->hypervadmin->enabled == '1' ? 'checked' : null,
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
					   ->setDefault($form->config->hypervadmin->URL)
					   ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'hypervadmin-enabled']));

		$hyperVAdminUsername = new Text('hypervadmin-username');
		$hyperVAdminUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->hypervadmin->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'hypervadmin-enabled']));

		$hyperVAdminPassword = new Password('hypervadmin-password');
		$hyperVAdminPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->config->hypervadmin->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'hypervadmin-enabled']));

		$deviceOptions[0] = 'Please select';
		foreach(Devices::Find() as $device) {
			$deviceOptions[$device->id] = $device->name;
		}
		$hyperVAdminDevice = new Select('hypervadmin-device', $deviceOptions, ['fieldset' => 'end']);
		$hyperVAdminDevice->setLabel('Host')
			->setDefault($form->config->hypervadmin->device)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'hypervadmin-enabled']));

		$form->add($hyperVAdminEnabled);
		$form->add($hyperVAdminURL);
		$form->add($hyperVAdminUsername);
		$form->add($hyperVAdminPassword);
		$form->add($hyperVAdminDevice);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->hypervadmin->enabled = isset($data['hypervadmin-enabled']) && $data['hypervadmin-enabled'] == 'on' ? '1' : '0';
        $config->hypervadmin->URL = $data['hypervadmin-url'];
        $config->hypervadmin->username = $data['hypervadmin-username'];
        $config->hypervadmin->password = $data['hypervadmin-password'];
        $config->hypervadmin->device = $data['hypervadmin-device'];
    }
}