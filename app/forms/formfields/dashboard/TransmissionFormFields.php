<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class TransmissionFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$transmissionEnabled = new Check('transmission-enabled');
		$transmissionEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->transmission->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Transmission'
		]);

		$transmissionURL = new Text('transmission-url');
		$transmissionURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->transmission->URL)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled']));

		$transmissionUsername = new Text('transmission-username');
		$transmissionUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->transmission->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled']));

		$transmissionPassword = new Password('transmission-password');
		$transmissionPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->config->transmission->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled']));

		$transmissionInterval = new Numeric('transmission-update-interval');
		$transmissionInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->transmission->updateInterval)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled']));

		$form->add($transmissionEnabled);
		$form->add($transmissionURL);
		$form->add($transmissionUsername);
		$form->add($transmissionPassword);
		$form->add($transmissionInterval);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->transmission->enabled = isset($data['transmission-enabled']) && $data['transmission-enabled'] == 'on' ? '1' : '0';
        $config->transmission->URL = $data['transmission-url'];
        $config->transmission->username = $data['transmission-username'];
        $config->transmission->password = $data['transmission-password'];
        $config->transmission->updateInterval = $data['transmission-update-interval'];
    }
}