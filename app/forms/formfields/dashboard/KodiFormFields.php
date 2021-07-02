<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class KodiFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $kodiEnabled = new Check('kodi-enabled');
		$kodiEnabled->setLabel('Enabled')
			->setAttributes([
				'value' => '1',
				'checked' => $this->form->settings->kodi->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Kodi'
		]);

		$this->fields[] = $kodiURL = new Text('kodi-url');
		$kodiURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $kodiUsername = new Text('kodi-username');
		$kodiUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->username)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']));

		$this->fields[] = $kodiPassword = new Password('kodi-password');
		$kodiPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($this->form->settings->kodi->password)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']));

        if ($this->form->settings->hibp->enabled)
        {
			$kodiPassword->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }

        $this->fields[] = $kodiDbVideo = new Text('kodi-dbmusic');
		$kodiDbVideo->setLabel('Music database')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->dbmusic)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']));

        $this->fields[] = $kodiDbVideo = new Text('kodi-dbvideo');
		$kodiDbVideo->setLabel('Video database')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->dbvideo)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']));

		$this->fields[] = $kodiDbMusic = new Text('kodi-dbhost');
		$kodiDbMusic->setLabel('Database host')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->dbhost)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']));

        $this->fields[] = $kodiDbMusic = new Text('kodi-dbuser');
		$kodiDbMusic->setLabel('Database user')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->dbuser)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']));

        $this->fields[] = $kodiDbMusic = new Password('kodi-dbpassword');
		$kodiDbMusic->setLabel('Database password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->dbpassword)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled']));

		$this->fields[] = $rotateMoviesInterval = new Numeric('kodi-rotate_movies_interval');
		$rotateMoviesInterval->setLabel('Rotate movies interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->rotate_movies_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);

		$this->fields[] = $rotateEpisodesInterval = new Numeric('kodi-rotate_episodes_interval');
		$rotateEpisodesInterval->setLabel('Rotate episode interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->kodi->rotate_episodes_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);

		$this->fields[] = $rotateAlbumsInterval = new Numeric('kodi-rotate_albums_interval');
		$rotateAlbumsInterval->setLabel('Rotate albums interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->kodi->rotate_albums_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'kodi-enabled'])
			]);
	}
}