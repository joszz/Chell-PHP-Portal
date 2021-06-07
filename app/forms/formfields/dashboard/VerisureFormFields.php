<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class VerisureFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$verisureEnabled = new Check('verisure-enabled');
		$verisureEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->settings->verisure->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Verisure'
		]);

		$verisureInterval = new Numeric('verisure-update-interval');
		$verisureInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->verisure->update_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled'])
			]);

        $verisureURL = new Text('verisure-url');
		$verisureURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->settings->verisure->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$verisureUsername = new Text('verisure-username');
		$verisureUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->verisure->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$verisurePassword = new Password('verisure-password');
		$verisurePassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($form->settings->verisure->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

        $verisurePin = new Password('verisure-pin');
		$verisurePin->setLabel('Pin')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->settings->verisure->securitycode);

		$form->add($verisureEnabled);
		$form->add($verisureInterval);
		$form->add($verisureURL);
		$form->add($verisureUsername);
		$form->add($verisurePassword);
		$form->add($verisurePin);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->verisure->enabled = isset($data['verisure-enabled']) && $data['verisure-enabled'] == 'on' ? '1' : '0';
        $settings->verisure->update_interval = $data['verisure-update-interval'];
        $settings->verisure->url = $data['verisure-url'];
        $settings->verisure->username = $data['verisure-username'];
        $settings->verisure->password = $data['verisure-password'];
        $settings->verisure->securitycode = $data['verisure-pin'];
    }
}