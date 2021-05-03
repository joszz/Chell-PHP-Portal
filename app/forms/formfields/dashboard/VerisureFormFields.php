<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class VerisureFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$verisureEnabled = new Check('verisure-enabled');
		$verisureEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->verisure->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Verisure'
		]);

		$verisureInterval = new Numeric('verisure-update-interval');
		$verisureInterval->setLabel('Verisure interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->verisure->updateInterval)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

        $verisureURL = new Text('verisure-url');
		$verisureURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->config->verisure->URL)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$verisureUsername = new Text('verisure-username');
		$verisureUsername->setLabel('Verisure username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->verisure->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$verisurePassword = new Password('verisure-password');
		$verisurePassword->setLabel('Verisure password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($form->config->verisure->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

        $verisurePin = new Password('verisure-pin');
		$verisurePin->setLabel('Verisure pin')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->verisure->securityCode);

		$form->add($verisureEnabled);
		$form->add($verisureInterval);
		$form->add($verisureURL);
		$form->add($verisureUsername);
		$form->add($verisurePassword);
		$form->add($verisurePin);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->verisure->enabled = isset($data['verisure-enabled']) && $data['verisure-enabled'] == 'on' ? '1' : '0';
        $config->verisure->updateInterval = $data['verisure-update-interval'];
        $config->verisure->URL = $data['verisure-url'];
        $config->verisure->username = $data['verisure-username'];
        $config->verisure->password = $data['verisure-password'];
        $config->verisure->securityCode = $data['verisure-pin'];
    }
}