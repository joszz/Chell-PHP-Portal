<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the Verisure plugin
 *
 * @package Formfields
 */
class SonosFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;
		$this->fields[] = $sonosEnabled = new Check('sonos-enabled', [
			'fieldset' => 'Sonos',
			'checked' => $this->form->settings->sonos->enabled->value == '1' ? 'checked' : null
		]);
		$sonosEnabled->setUserOptions(['buttons' => ['sonos_request_authorization_code']]);

		$this->fields[] = $sonosAPIKey = new Password('sonos-api_key');
		$sonosAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sonos->api_key->value)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sonos-enabled'])
			]);

		$session = $this->form->di->get('session');
        if (!$session->get('sonos_state'))
        {
            $session->set('sonos_state', bin2hex(random_bytes(32)));
		}

		$this->fields[] = $sonosAPISecret = new Password('sonos-api_secret');
		$sonosAPISecret->setLabel('API secret')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sonos->api_secret->value)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sonos-enabled'])
			]);

        $this->fields[] = $sonosHouseholds = new Select('sonos-household');
		$sonosHouseholds->setLabel('Household')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'disabled' => true,
				'data-selected' => $this->form->settings->sonos->household->value ?? '',
				'data-apiurl' => '../sonos/households'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);

        $this->fields[] = $sonosGroups = new Select('sonos-group');
		$sonosGroups->setLabel('Group')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'disabled' => true,
				'data-selected' => $this->form->settings->sonos->group->value ?? '',
				'data-apiurl' => '../sonos/groups'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);

		$this->fields[] = $sonosInterval = new Numeric('sonos-update_interval');
		$sonosInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sonos->update_interval->value ?? 30)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sonos-enabled'])
			]);
	}
}