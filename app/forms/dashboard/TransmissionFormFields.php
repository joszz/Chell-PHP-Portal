<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class TransmissionFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$transmissionEnabled = new Check('transmission-enabled');
		$transmissionEnabled->setLabel('Enabled');
		$transmissionEnabled->setAttributes([
			'checked' => $form->_config->transmission->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->_config->transmission->URL);

		$transmissionUsername = new Text('transmission-username');
		$transmissionUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->transmission->username);

		$transmissionPassword = new Password('transmission-password');
		$transmissionPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->_config->transmission->password);

		$transmissionInterval = new Numeric('transmission-update-interval');
		$transmissionInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->transmission->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$form->add($transmissionEnabled);
		$form->add($transmissionURL);
		$form->add($transmissionUsername);
		$form->add($transmissionPassword);
		$form->add($transmissionInterval);
	}

    public static function setPostData(&$config, $data)
    {
        $config->transmission->enabled = isset($data['transmission-enabled']) && $data['transmission-enabled'] == 'on' ? '1' : '0';
        $config->transmission->URL = $data['transmission-url'];
        $config->transmission->username = $data['transmission-username'];
        $config->transmission->password = $data['transmission-password'];
        $config->transmission->updateInterval = $data['transmission-update-interval'];
    }
}