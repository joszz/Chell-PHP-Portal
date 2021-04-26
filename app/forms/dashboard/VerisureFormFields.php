<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class VerisureFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$verisureEnabled = new Check('verisure-enabled');
		$verisureEnabled->setLabel('Enabled');
		$verisureEnabled->setAttributes([
			'checked' => $form->_config->verisure->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->_config->verisure->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

        $verisureURL = new Text('verisure-url');
		$verisureURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->_config->verisure->URL);

		$verisureUsername = new Text('verisure-username');
		$verisureUsername->setLabel('Verisure username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->verisure->username);

		$verisurePassword = new Password('verisure-password');
		$verisurePassword->setLabel('Verisure password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($form->_config->verisure->password);

        $verisurePin = new Password('verisure-pin');
		$verisurePin->setLabel('Verisure pin')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->verisure->securityCode);

		$form->add($verisureEnabled);
		$form->add($verisureInterval);
		$form->add($verisureURL);
		$form->add($verisureUsername);
		$form->add($verisurePassword);
		$form->add($verisurePin);
	}

    public static function setPostData(&$config, $data)
    {
        $config->verisure->enabled = isset($data['verisure-enabled']) && $data['verisure-enabled'] == 'on' ? '1' : '0';
        $config->verisure->updateInterval = $data['verisure-update-interval'];
        $config->verisure->URL = $data['verisure-url'];
        $config->verisure->username = $data['verisure-username'];
        $config->verisure->password = $data['verisure-password'];
        $config->verisure->securityCode = $data['verisure-pin'];
    }
}