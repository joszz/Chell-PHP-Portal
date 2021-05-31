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
			'checked' => $form->settings->phpsysinfo->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'PHPSysInfo'
		]);

		$phpSysInfoURL = new Text('phpsysinfo-url');
		$phpSysInfoURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->phpsysinfo->url)
			->addValidators([
				new PresenceOf(['message' => $form->translator->validation['required']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'phpsysinfo-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$phpSysInfoUsername = new Text('phpsysinfo-username');
		$phpSysInfoUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->phpsysinfo->username);

		$phpSysInfoPassword = new Password('phpsysinfo-password');
		$phpSysInfoPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => 'end'])
			->setDefault($form->settings->phpsysinfo->password);

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
    public function setPostData(&$settings, $data)
    {
        $settings->phpsysinfo->enabled = isset($data['phpsysinfo-enabled']) && $data['phpsysinfo-enabled'] == 'on' ? '1' : '0';
        $settings->phpsysinfo->url = $data['phpsysinfo-url'];
        $settings->phpsysinfo->username = $data['phpsysinfo-username'];
        $settings->phpsysinfo->password = $data['phpsysinfo-password'];
    }
}