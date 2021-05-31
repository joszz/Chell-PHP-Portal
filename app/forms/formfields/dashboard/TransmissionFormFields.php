<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

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
				'checked' => $form->settings->transmission->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->settings->transmission->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$transmissionUsername = new Text('transmission-username');
		$transmissionUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->transmission->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled']));

		$transmissionPassword = new Password('transmission-password');
		$transmissionPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->settings->transmission->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled']));

		$transmissionInterval = new Numeric('transmission-update-interval');
		$transmissionInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->settings->transmission->update_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'transmission-enabled'])
			]);

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
    public function setPostData(&$settings, $data)
    {
        $settings->transmission->enabled = isset($data['transmission-enabled']) && $data['transmission-enabled'] == 'on' ? '1' : '0';
        $settings->transmission->url = $data['transmission-url'];
        $settings->transmission->username = $data['transmission-username'];
        $settings->transmission->password = $data['transmission-password'];
        $settings->transmission->update_interval = $data['transmission-update-interval'];
    }
}