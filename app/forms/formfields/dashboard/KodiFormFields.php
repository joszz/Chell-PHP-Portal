<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
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
	public function setFields($form)
	{
		$kodiEnabled = new Check('kodi-enabled');
		$kodiEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->kodi->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->config->kodi->URL)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$kodiUsername = new Text('kodi-username');
		$kodiUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->kodi->username)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled']));

		$kodiPassword = new Password('kodi-password');
		$kodiPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->config->kodi->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled']));

		$rotateMoviesInterval = new Numeric('kodi-rotate-movies-interval');
		$rotateMoviesInterval->setLabel('Rotate movies interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->kodi->rotateMoviesInterval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);

		$rotateEpisodesInterval = new Numeric('kodi-rotate-episodes-interval');
		$rotateEpisodesInterval->setLabel('Rotate episode interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->kodi->rotateEpisodesInterval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);

		$rotateAlbumsInterval = new Numeric('kodi-rotate-albums-interval');
		$rotateAlbumsInterval->setLabel('Rotate albums interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->kodi->rotateAlbumsInterval)
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
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->kodi->enabled = isset($data['kodi-enabled']) && $data['kodi-enabled'] == 'on' ? '1' : '0';
        $config->kodi->URL = $data['kodi-url'];
        $config->kodi->username = $data['kodi-username'];
        $config->kodi->password = $data['kodi-password'];
        $config->kodi->rotateMoviesInterval = $data['kodi-rotate-movies-interval'];
        $config->kodi->rotateEpisodesInterval = $data['kodi-rotate-episodes-interval'];
        $config->kodi->rotateAlbumsInterval = $data['kodi-rotate-albums-interval'];
    }
}