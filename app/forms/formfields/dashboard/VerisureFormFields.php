<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class VerisureFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $verisureEnabled = new Check('verisure-enabled');
		$verisureEnabled->setLabel('Enabled')
			->setAttributes([
				'value' => '1',
				'checked' => $this->form->settings->verisure->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Verisure'
		]);

		$this->fields[] = $verisureInterval = new Numeric('verisure-update_interval');
		$verisureInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->verisure->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'verisure-enabled'])
			]);

        $this->fields[] = $verisureURL = new Text('verisure-url');
		$verisureURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->verisure->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'verisure-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $verisureUsername = new Text('verisure-username');
		$verisureUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->verisure->username)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$this->fields[] = $verisurePassword = new Password('verisure-password');
		$verisurePassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($this->form->settings->verisure->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'verisure-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$verisurePassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

        $this->fields[] = $verisurePin = new Password('verisure-securitycode');
		$verisurePin->setLabel('Pin')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->verisure->securitycode);
	}
}