<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;

class SickrageFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$sickrageEnabled = new Check('sickrage-enabled');
		$sickrageEnabled->setLabel('Enabled');
		$sickrageEnabled->setAttributes([
			'checked' => $form->_config->sickrage->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Sickrage'
		]);

		$sickrageURL = new Text('sickrage-url');
		$sickrageURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->sickrage->URL);

		$sickrageAPIKey = new Password('sickrage-apikey');
		$sickrageAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->sickrage->APIKey);

		$form->add($sickrageEnabled);
		$form->add($sickrageURL);
		$form->add($sickrageAPIKey);
	}

    public static function setPostData(&$config, $data)
    {
        $config->sickrage->enabled = isset($data['sickrage-enabled']) && $data['sickrage-enabled'] == 'on' ? '1' : '0';
        $config->sickrage->URL = $data['sickrage-url'];
        $config->sickrage->APIKey = $data['sickrage-apikey'];
    }
}