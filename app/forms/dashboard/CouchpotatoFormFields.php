<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class CouchpotatoFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$couchpotatoEnabled = new Check('couchpotato-enabled');
		$couchpotatoEnabled->setLabel('Enabled');
		$couchpotatoEnabled->setAttributes([
			'checked' => $form->_config->couchpotato->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Couchpotato'
		]);

		$couchpotatoURL = new Text('couchpotato-url');
		$couchpotatoURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->couchpotato->URL);

		$couchpotatoAPIKey = new Password('couchpotato-apikey');
		$couchpotatoAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->couchpotato->APIKey);

		$rotateInterval = new Numeric('couchpotato-rotate-interval');
		$rotateInterval->setLabel('Rotate interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->couchpotato->rotateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$form->add($couchpotatoEnabled);
		$form->add($couchpotatoURL);
		$form->add($couchpotatoAPIKey);
		$form->add($rotateInterval);
	}

    public static function setPostData(&$config, $data)
    {
        $config->couchpotato->enabled = isset($data['couchpotato-enabled']) && $data['couchpotato-enabled'] == 'on' ? '1' : '0';
        $config->couchpotato->URL = $data['couchpotato-url'];
        $config->couchpotato->APIKey = $data['couchpotato-apikey'];
        $config->couchpotato->rotateInterval = $data['couchpotato-rotate-interval'];
    }
}