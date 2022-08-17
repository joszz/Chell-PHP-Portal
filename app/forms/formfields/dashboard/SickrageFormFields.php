<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the Sickrage plugin
 *
 * @package Formfields
 */
class SickrageFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('sickrage-enabled', [
			'fieldset' => 'Sickrage',
			'checked' => $this->form->settings->sickrage->enabled == '1' ? 'checked' : null,
		]);

		$this->fields[] = $sickrageURL = new Text('sickrage-url');
		$sickrageURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sickrage->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sickrage-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $sickrageAPIKey = new Password('sickrage-api_key');
		$sickrageAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sickrage->api_key)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sickrage-enabled']));
	}
}