<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class KodiFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$kodiEnabled = new Check('kodi-enabled');
		$kodiEnabled->setLabel('Enabled');
		$kodiEnabled->setAttributes([
			'checked' => $form->_config->kodi->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->_config->kodi->URL);

		$kodiUsername = new Text('kodi-username');
		$kodiUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->kodi->username);

		$kodiPassword = new Password('kodi-password');
		$kodiPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($form->_config->kodi->password);

		$rotateMoviesInterval = new Numeric('kodi-rotate-movies-interval');
		$rotateMoviesInterval->setLabel('Rotate movies interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->kodi->rotateMoviesInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$rotateEpisodesInterval = new Numeric('kodi-rotate-episodes-interval');
		$rotateEpisodesInterval->setLabel('Rotate episode interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->kodi->rotateEpisodesInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$rotateAlbumsInterval = new Numeric('kodi-rotate-albums-interval');
		$rotateAlbumsInterval->setLabel('Rotate albums interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->kodi->rotateAlbumsInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

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