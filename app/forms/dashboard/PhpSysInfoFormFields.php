<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;

class PhpSysInfoFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$phpSysInfoEnabled = new Check('phpsysinfo-enabled');
		$phpSysInfoEnabled->setLabel('Enabled');
		$phpSysInfoEnabled->setAttributes([
			'checked' => $form->_config->phpsysinfo->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'PHPSysInfo'
		]);

		$phpSysInfoURL = new Text('phpsysinfo-url');
		$phpSysInfoURL->setLabel('PHPSysInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->phpsysinfo->URL)
			->addValidators([new PresenceOf([])]);

		$phpSysInfoUsername = new Text('phpsysinfo-username');
		$phpSysInfoUsername->setLabel('PHPSysInfo username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->phpsysinfo->username);

		$phpSysInfoPassword = new Password('phpsysinfo-password');
		$phpSysInfoPassword->setLabel('PHPSysInfo password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => 'end'])
			->setDefault($form->_config->phpsysinfo->password);

		$form->add($phpSysInfoEnabled);
		$form->add($phpSysInfoURL);
		$form->add($phpSysInfoUsername);
		$form->add($phpSysInfoPassword);
	}

    public static function setPostData(&$config, $data)
    {
        $config->phpsysinfo->enabled = isset($data['phpsysinfo-enabled']) && $data['phpsysinfo-enabled'] == 'on' ? '1' : '0';
        $config->phpsysinfo->URL = $data['phpsysinfo-url'];
        $config->phpsysinfo->username = $data['phpsysinfo-username'];
        $config->phpsysinfo->password = $data['phpsysinfo-password'];
    }
}