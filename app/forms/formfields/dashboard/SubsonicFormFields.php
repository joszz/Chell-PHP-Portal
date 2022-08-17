<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the Subsonic plugin
 *
 * @package Formfields
 */
class SubsonicFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('subsonic-enabled', [
			'fieldset' => 'Subsonic',
			'checked' => $this->form->settings->subsonic->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $subsonicURL = new Text('subsonic-url');
		$subsonicURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->subsonic->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'subsonic-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $subsonicUsername = new Text('subsonic-username');
		$subsonicUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->subsonic->username)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'subsonic-enabled']));

		$this->fields[] = $subsonicPassword = new Password('subsonic-password');
		$subsonicPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password'])
			->setDefault($this->form->settings->subsonic->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'subsonic-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$subsonicPassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }
	}
}