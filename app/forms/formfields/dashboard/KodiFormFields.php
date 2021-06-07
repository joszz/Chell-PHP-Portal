<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class KodiFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$kodiEnabled = new Check('kodi-enabled');
		$kodiEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->settings->kodi->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Kodi'
		]);

		$kodiURL = new Text('kodi-url');
		$kodiURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->kodi->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$kodiUsername = new Text('kodi-username');
		$kodiUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->kodi->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled']));

		$kodiPassword = new Password('kodi-password');
		$kodiPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->settings->kodi->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled']));

		$rotateMoviesInterval = new Numeric('kodi-rotate-movies-interval');
		$rotateMoviesInterval->setLabel('Rotate movies interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->kodi->rotate_movies_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);

		$rotateEpisodesInterval = new Numeric('kodi-rotate-episodes-interval');
		$rotateEpisodesInterval->setLabel('Rotate episode interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->kodi->rotate_episodes_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);

		$rotateAlbumsInterval = new Numeric('kodi-rotate-albums-interval');
		$rotateAlbumsInterval->setLabel('Rotate albums interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->settings->kodi->rotate_albums_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);

		$form->add($kodiEnabled);
		$form->add($kodiURL);
		$form->add($kodiUsername);
		$form->add($kodiPassword);
		$form->add($rotateMoviesInterval);
		$form->add($rotateEpisodesInterval);
		$form->add($rotateAlbumsInterval);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->kodi->enabled = isset($data['kodi-enabled']) && $data['kodi-enabled'] == 'on' ? '1' : '0';
        $settings->kodi->url = $data['kodi-url'];
        $settings->kodi->username = $data['kodi-username'];
        $settings->kodi->password = $data['kodi-password'];
        $settings->kodi->rotate_movies_interval = $data['kodi-rotate-movies-interval'];
        $settings->kodi->rotate_episodes_interval = $data['kodi-rotate-episodes-interval'];
        $settings->kodi->rotate_albums_interval = $data['kodi-rotate-albums-interval'];
    }
}