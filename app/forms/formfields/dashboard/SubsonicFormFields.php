<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Url as UrlValidator;

class SubsonicFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $subsonicEnabled = new Check('subsonic-enabled');
		$subsonicEnabled->setLabel('Enabled')
			->setAttributes([
				'value' => '1',
				'checked' => $this->form->settings->subsonic->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Subsonic'
		]);

		$this->fields[] = $subsonicURL = new Text('subsonic-url');
		$subsonicURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->subsonic->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'subsonic-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url']])
			]);

		$this->fields[] = $subsonicUsername = new Text('subsonic-username');
		$subsonicUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->subsonic->username)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'subsonic-enabled']));

		$this->fields[] = $subsonicPassword = new Password('subsonic-password');
		$subsonicPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end', 'autocomplete' => 'new-password'])
			->setDefault($this->form->settings->subsonic->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'subsonic-enabled']));
	}
}