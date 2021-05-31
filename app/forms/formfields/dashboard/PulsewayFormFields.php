<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class PulsewayFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$pulsewayEnabled = new Check('pulseway-enabled');
		$pulsewayEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->settings->pulseway->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Pulseway'
		]);

		$pulsewayURL = new Text('pulseway-url');
		$pulsewayURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->pulseway->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'pulseway-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

        $pulsewayUsername = new Text('pulseway-username');
		$pulsewayUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->pulseway->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'pulseway-enabled']));

		$pulsewayPassword = new Password('pulseway-password');
		$pulsewayPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($form->settings->pulseway->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'pulseway-enabled']));

        $pulsewayInterval = new Numeric('pulseway-update-interval');
		$pulsewayInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->pulseway->update_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'pulseway-enabled'])
			]);
		
        $pulsewaySystems = new Select('pulseway-systems[]');
		$pulsewaySystems->setLabel('Systems')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'multiple' => 'multiple',
				'fieldset' => 'end',
				'disabled' => true,
				'data-selected' => $form->settings->pulseway->systems,
				'data-apiurl' => '../pulseway/systems'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);

		$form->add($pulsewayEnabled);
		$form->add($pulsewayURL);
		$form->add($pulsewayUsername);
		$form->add($pulsewayPassword);
		$form->add($pulsewayInterval);
		$form->add($pulsewaySystems);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$settings, $data)
    {
        $settings->pulseway->enabled = isset($data['pulseway-enabled']) && $data['pulseway-enabled'] == 'on' ? '1' : '0';
        $settings->pulseway->url = $data['pulseway-url'];
        $settings->pulseway->username = $data['pulseway-username'];
		$settings->pulseway->password = $data['pulseway-password'];
		$settings->pulseway->update_interval = $data['pulseway-update-interval'];
		$settings->pulseway->systems = implode($data['pulseway-systems'], ',');
    }
}