<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the Pulseway plugin
 *
 * @package Formfields
 */
class PulsewayFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('pulseway-enabled', [
			'fieldset' => 'Pulseway',
			'checked' => $this->form->settings->pulseway->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $pulsewayURL = new Text('pulseway-url');
		$pulsewayURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->pulseway->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'pulseway-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

        $this->fields[] = $pulsewayUsername = new Text('pulseway-username');
		$pulsewayUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->pulseway->username)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'pulseway-enabled']));

		$this->fields[] = $pulsewayPassword = new Password('pulseway-password');
		$pulsewayPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password'])
			->setDefault($this->form->settings->pulseway->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'pulseway-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$pulsewayPassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

        $this->fields[] = $pulsewaySystems = new Select('pulseway-systems[]');
		$pulsewaySystems->setLabel('Systems')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'multiple' => 'multiple',
				'disabled' => true,
				'data-selected' => $this->form->settings->pulseway->systems ?? '',
				'data-apiurl' => '../pulseway/systems'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);

        $this->fields[] = $pulsewayInterval = new Numeric('pulseway-update_interval');
		$pulsewayInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->pulseway->update_interval ?? 30)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'pulseway-enabled'])
			]);
	}
}