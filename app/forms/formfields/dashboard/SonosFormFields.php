<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;

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
		$this->fields[] = new Check('sonos-enabled', [
			'fieldset' => 'Sonos',
			'checked' => $this->form->settings->sonos->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $sonosAPIKey = new Password('sonos-api_key');
		$sonosAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sonos->api_key)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sonos-enabled'])
			]);

		$this->fields[] = $sonosAPISecret = new Password('sonos-api_secret');
		$sonosAPISecret->setLabel('API secret')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sonos->api_secret)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sonos-enabled'])
			])->setUserOptions(['buttons' => ['sonos_request_authorization_code']]);

        $this->fields[] = $pulsewaySystems = new Select('sonos-household_id');
		$pulsewaySystems->setLabel('Household')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'disabled' => true,
				'data-selected' => $this->form->settings->sonos->household_id ?? '',
				'data-apiurl' => '../sonos/households'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);

        $this->fields[] = $pulsewaySystems = new Select('sonos-group_id');
		$pulsewaySystems->setLabel('Group')
			->setFilters(['striptags', 'string'])
			->setAttributes([
				'class' => 'form-control',
				'disabled' => true,
				'data-selected' => $this->form->settings->sonos->group_id ?? '',
				'data-apiurl' => '../sonos/groups'
			])
			->setUserOptions(['buttons' => ['refresh_api_data']]);
	}
}