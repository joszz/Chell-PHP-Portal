<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class SpeedtestFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$speedtestEnabled = new Check('speedtest-enabled');
		$speedtestEnabled->setLabel('Enabled');
		$speedtestEnabled->setAttributes([
			'checked' => $form->settings->speedtest->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Speedtest'
		]);

		$speedtestTestOrder = new Text('speedtest-test-order');
		$speedtestTestOrder->setLabel('Test order')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->speedtest->test_order)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestUpTime = new Numeric('speedtest-time-ul');
		$speedtestUpTime->setLabel('Upload time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->speedtest->time_upload)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled'])
			]);

		$speedtestDownloadTime = new Numeric('speedtest-time-dl');
		$speedtestDownloadTime->setLabel('Download time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->speedtest->time_download)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled'])
			]);

		$speedtestGetIP = new Check('speedtest-get-ispip');
		$speedtestGetIP->setLabel('Get ISP IP');
		$speedtestGetIP->setAttributes([
			'checked' => $form->settings->speedtest->get_isp_info == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => true
		]);

		$speedtestISPInfo = new Select('speedtest-isp-info-distance', ['km' => 'Kilometers', 'mi' => 'Miles']);
		$speedtestISPInfo->setLabel('Distance units')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->speedtest->get_isp_distance)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestTelemetry = new Select('speedtest-telemetry', ['off' => 'Off', 'basic' => 'Basic', 'full' => 'Full']);
		$speedtestTelemetry->setLabel('Telemetry')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->settings->speedtest->telemetry)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestIpInfoURL = new Text('speedtest-ipinfo-url');
		$speedtestIpInfoURL->setLabel('IPInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->settings->speedtest->ip_info_url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$speedtestIpInfoToken = new Password('speedtest-ipinfo-token');
		$speedtestIpInfoToken->setLabel('IPInfo token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->speedtest->ip_info_token)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

        $whatIsMyBrowserAPIURL = new Text('speedtest-whatismybrowser-apiurl');
        $whatIsMyBrowserAPIURL->setLabel('WhatIsMyBrowser API URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->setDefault($form->settings->speedtest->what_is_my_browser_api_url)
            ->addValidator(new UrlValidator(['message' => $form->translator->validation['url']]));

        $whatIsMyBrowserAPIKey = new Password('speedtest-whatismybrowser-apikey');
        $whatIsMyBrowserAPIKey->setLabel('WhatIsMyBrowser API key')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
            ->setDefault($form->settings->speedtest->what_is_my_browser_api_key);

		$form->add($speedtestEnabled);
		$form->add($speedtestTestOrder);
		$form->add($speedtestUpTime);
		$form->add($speedtestDownloadTime);
		$form->add($speedtestGetIP);
		$form->add($speedtestISPInfo);
		$form->add($speedtestTelemetry);
		$form->add($speedtestIpInfoURL);
		$form->add($speedtestIpInfoToken);
		$form->add($whatIsMyBrowserAPIURL);
		$form->add($whatIsMyBrowserAPIKey);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
		$settings->speedtest->enabled = isset($data['speedtest-enabled']) && $data['speedtest-enabled'] == 'on' ? '1' : '0';
		$settings->speedtest->test_order = $data['speedtest-test-order'];
		$settings->speedtest->time_upload = $data['speedtest-time-ul'];
		$settings->speedtest->time_download = $data['speedtest-time-dl'];
		$settings->speedtest->get_isp_info = isset($data['speedtest-get-ispip']) && $data['speedtest-get-ispip'] == 'on' ? '1' : '0';
		$settings->speedtest->get_isp_distance = $data['speedtest-isp-info-distance'];
		$settings->speedtest->ip_info_url = $data['speedtest-ipinfo-url'];
		$settings->speedtest->ip_info_token = $data['speedtest-ipinfo-token'];
		$settings->speedtest->what_is_my_browser_api_url = $data['speedtest-whatismybrowser-apiurl'];
		$settings->speedtest->what_is_my_browser_api_key = $data['speedtest-whatismybrowser-apikey'];
    }
}