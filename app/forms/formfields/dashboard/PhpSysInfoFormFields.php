<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

class PhpSysInfoFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = new Check('phpsysinfo-enabled', [
			'fieldset' => 'PHPSysInfo',
			'checked' => $this->form->settings->phpsysinfo->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $phpSysInfoURL = new Text('phpsysinfo-url');
		$phpSysInfoURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->phpsysinfo->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'phpsysinfo-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $phpSysInfoUsername = new Text('phpsysinfo-username');
		$phpSysInfoUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->phpsysinfo->username);

		$this->fields[] = $phpSysInfoPassword = new Password('phpsysinfo-password');
		$phpSysInfoPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => 'end'])
			->setDefault($this->form->settings->phpsysinfo->password);

        if ($this->form->settings->hibp->enabled)
        {
			$phpSysInfoPassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }
	}
}