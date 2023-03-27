<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the Verisure plugin
 *
 * @package Formfields
 */
class VerisureFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('verisure-enabled', [
			'fieldset' => 'Verisure',
			'checked' => $this->form->settings->verisure->enabled->value == '1' ? 'checked' : null
		]);

		$this->fields[] = $verisureUsername = new Text('verisure-username');
		$verisureUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->verisure->username->value)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$this->fields[] = $verisurePassword = new Password('verisure-password');
		$verisurePassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password'])
			->setDefault($this->form->settings->verisure->password->value)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'verisure-enabled']));

        if ($this->form->settings->hibp->enabled->value)
        {
			$verisurePassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

        $this->fields[] = $verisurePin = new Password('verisure-securitycode');
		$verisurePin->setLabel('Pin')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->verisure->securitycode->value);

		$this->fields[] = $verisureInterval = new Numeric('verisure-update_interval');
		$verisureInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->verisure->update_interval->value ?? 180)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'verisure-enabled'])
			]);
	}
}