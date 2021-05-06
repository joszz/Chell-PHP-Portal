<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Url as UrlValidator;

class SubsonicFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$subsonicEnabled = new Check('subsonic-enabled');
		$subsonicEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->subsonic->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Subsonic'
		]);

		$subsonicURL = new Text('subsonic-url');
		$subsonicURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->subsonic->URL)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'subsonic-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$subsonicUsername = new Text('subsonic-username');
		$subsonicUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->subsonic->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'subsonic-enabled']));

		$subsonicPassword = new Password('subsonic-password');
		$subsonicPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end', 'autocomplete' => 'new-password'])
			->setDefault($form->config->subsonic->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'subsonic-enabled']));

		$form->add($subsonicEnabled);
		$form->add($subsonicURL);
		$form->add($subsonicUsername);
		$form->add($subsonicPassword);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
	public function setPostData(&$config, $data)
    {
        $config->subsonic->enabled = isset($data['subsonic-enabled']) && $data['subsonic-enabled'] == 'on' ? '1' : '0';
        $config->subsonic->URL = $data['subsonic-url'];
        $config->subsonic->username = $data['subsonic-username'];
        $config->subsonic->password = $data['subsonic-password'];
    }
}