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

class PsaRemoteFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $psaremoteEnabled = new Check('psa_remote-enabled');
		$psaremoteEnabled->setLabel('Enabled')
			->setAttributes([
				'value' => '1',
				'checked' => $this->form->settings->psa_remote->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'PSA Remote'
		]);

		$this->fields[] = $psaremoteURL = new Text('psa_remote-url');
		$psaremoteURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->psa_remote->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psa_remote-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

        $this->fields[] = $psaremoteUsername = new Text('psa_remote-username');
		$psaremoteUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->psa_remote->username)

			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'pulseway-enabled']));
		$this->fields[] = $psaremotePassword = new Password('psa_remote-password');
		$psaremotePassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($this->form->settings->psa_remote->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psa_remote-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$psaremotePassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

        $this->fields[] = $psaremoteInterval = new Password('psa_remote-vin');
		$psaremoteInterval->setLabel('VIN')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->psa_remote->vin)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psa_remote-enabled'])
			]);

		$this->fields[] = $psaremoteInterval = new Numeric('psa_remote-update_interval');
		$psaremoteInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->psa_remote->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'psa_remote-enabled'])
			]);
	}
}