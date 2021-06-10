<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Url as UrlValidator;

class SickrageFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $sickrageEnabled = new Check('sickrage-enabled');
		$sickrageEnabled->setLabel('Enabled');
		$sickrageEnabled->setAttributes([
			'value' => '1',
			'checked' => $this->form->settings->sickrage->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Sickrage'
		]);

		$this->fields[] = $sickrageURL = new Text('sickrage-url');
		$sickrageURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->sickrage->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sickrage-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url']])
			]);

		$this->fields[] = $sickrageAPIKey = new Password('sickrage-api_key');
		$sickrageAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->sickrage->api_key)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sickrage-enabled']));
	}
}