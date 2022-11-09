<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;

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
			])->setUserOptions(['buttons' => ['sonos_request_authorization_code']]);;
	}
}