<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Url as UrlValidator;

class SickrageFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$sickrageEnabled = new Check('sickrage-enabled');
		$sickrageEnabled->setLabel('Enabled');
		$sickrageEnabled->setAttributes([
			'checked' => $form->settings->sickrage->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->settings->sickrage->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'sickrage-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$sickrageAPIKey = new Password('sickrage-apikey');
		$sickrageAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->settings->sickrage->api_key)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'sickrage-enabled']));

		$form->add($sickrageEnabled);
		$form->add($sickrageURL);
		$form->add($sickrageAPIKey);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->sickrage->enabled = isset($data['sickrage-enabled']) && $data['sickrage-enabled'] == 'on' ? '1' : '0';
        $settings->sickrage->url = $data['sickrage-url'];
        $settings->sickrage->api_key = $data['sickrage-apikey'];
    }
}