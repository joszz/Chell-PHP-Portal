<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;

class OpcacheFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$opcacheEnabled = new Check('opcache-enabled');
		$opcacheEnabled->setLabel('Enabled');
		$opcacheEnabled->setAttributes([
			'checked' => $form->_config->opcache->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Opcache'
		]);

		$opcacheHidden = new Hidden('opcache-hidden');
		$opcacheHidden->setLabel('');
		$opcacheHidden->setAttributes(['fieldset' => 'end']);

		$form->add($opcacheEnabled);
		$form->add($opcacheHidden);
	}

    public static function setPostData(&$config, $data)
    {
        $config->opcache->enabled = isset($data['opcache-enabled']) && $data['opcache-enabled'] == 'on' ? '1' : '0';
    }
}