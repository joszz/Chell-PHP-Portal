<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;

class SubsonicFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$subsonicEnabled = new Check('subsonic-enabled');
		$subsonicEnabled->setLabel('Enabled');
		$subsonicEnabled->setAttributes([
			'checked' => $form->_config->subsonic->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->_config->subsonic->URL);

		$subsonicUsername = new Text('subsonic-username');
		$subsonicUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->subsonic->username);

		$subsonicPassword = new Password('subsonic-password');
		$subsonicPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end', 'autocomplete' => 'new-password'])
			->setDefault($form->_config->subsonic->password);

		$form->add($subsonicEnabled);
		$form->add($subsonicURL);
		$form->add($subsonicUsername);
		$form->add($subsonicPassword);
	}

	public static function setPostData(&$config, $data)
    {
        $config->subsonic->enabled = isset($data['subsonic-enabled']) && $data['subsonic-enabled'] == 'on' ? '1' : '0';
        $config->subsonic->URL = $data['subsonic-url'];
        $config->subsonic->username = $data['subsonic-username'];
        $config->subsonic->password = $data['subsonic-password'];
    }
}