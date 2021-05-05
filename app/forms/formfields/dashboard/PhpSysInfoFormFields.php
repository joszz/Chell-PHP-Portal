<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Url as UrlValidator;

class PhpSysInfoFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$phpSysInfoEnabled = new Check('phpsysinfo-enabled');
		$phpSysInfoEnabled->setLabel('Enabled');
		$phpSysInfoEnabled->setAttributes([
			'checked' => $form->config->phpsysinfo->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->config->phpsysinfo->URL)
			->addValidators([
				new PresenceOf(['message' => $form->translator->validation['required']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'phpsysinfo-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$phpSysInfoUsername = new Text('phpsysinfo-username');
		$phpSysInfoUsername->setLabel('PHPSysInfo username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->phpsysinfo->username);

		$phpSysInfoPassword = new Password('phpsysinfo-password');
		$phpSysInfoPassword->setLabel('PHPSysInfo password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => 'end'])
			->setDefault($form->config->phpsysinfo->password);

		$form->add($phpSysInfoEnabled);
		$form->add($phpSysInfoURL);
		$form->add($phpSysInfoUsername);
		$form->add($phpSysInfoPassword);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->phpsysinfo->enabled = isset($data['phpsysinfo-enabled']) && $data['phpsysinfo-enabled'] == 'on' ? '1' : '0';
        $config->phpsysinfo->URL = $data['phpsysinfo-url'];
        $config->phpsysinfo->username = $data['phpsysinfo-username'];
        $config->phpsysinfo->password = $data['phpsysinfo-password'];
    }
}