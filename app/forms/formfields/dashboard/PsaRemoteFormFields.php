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
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the PSA Car Controller plugin
 *
 * @package Formfields
 */
class PsaRemoteFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('psaremote-enabled', [
			'fieldset' => 'PSA Remote',
			'checked' => $this->form->settings->psaremote->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $psaremoteURL = new Text('psaremote-url');
		$psaremoteURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->psaremote->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psaremote-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

        $this->fields[] = $psaremoteUsername = new Text('psaremote-username');
		$psaremoteUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->psaremote->username)

			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psaremote-enabled']));
		$this->fields[] = $psaremotePassword = new Password('psaremote-password');
		$psaremotePassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password'])
			->setDefault($this->form->settings->psaremote->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psaremote-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$psaremotePassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

        $this->fields[] = $psaremoteInterval = new Password('psaremote-vin');
		$psaremoteInterval->setLabel('VIN')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->psaremote->vin)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psaremote-enabled'])
			]);

		$this->fields[] = $psaremoteInterval = new Numeric('psaremote-update_interval');
		$psaremoteInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->psaremote->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psaremote-enabled'])
			]);
	}
}