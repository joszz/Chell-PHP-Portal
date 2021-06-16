<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class SpeedtestFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $speedtestEnabled = new Check('speedtest-enabled');
		$speedtestEnabled->setLabel('Enabled');
		$speedtestEnabled->setAttributes([
			'value' => '1',
			'checked' => $this->form->settings->speedtest->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Speedtest'
		]);

		$this->fields[] = $speedtestTestOrder = new Text('speedtest-test_order');
		$speedtestTestOrder->setLabel('Test order')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->speedtest->test_order)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$this->fields[] = $speedtestUpTime = new Numeric('speedtest-time_upload');
		$speedtestUpTime->setLabel('Upload time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->speedtest->time_upload)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled'])
			]);

		$this->fields[] = $speedtestDownloadTime = new Numeric('speedtest-time_download');
		$speedtestDownloadTime->setLabel('Download time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->speedtest->time_download)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled'])
			]);

		$this->fields[] = $speedtestGetIP = new Check('speedtest-get_isp_info');
		$speedtestGetIP->setLabel('Get ISP IP');
		$speedtestGetIP->setAttributes([
			'value' => '1',
			'checked' => $this->form->settings->speedtest->get_isp_info == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => true
		]);

		$this->fields[] = $speedtestISPInfo = new Select('speedtest-get_isp_distance', ['km' => 'Kilometers', 'mi' => 'Miles']);
		$speedtestISPInfo->setLabel('Distance units')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->speedtest->get_isp_distance)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$this->fields[] = $speedtestTelemetry = new Select('speedtest-telemetry', ['off' => 'Off', 'basic' => 'Basic', 'full' => 'Full']);
		$speedtestTelemetry->setLabel('Telemetry')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->telemetry)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$this->fields[] = $speedtestIpInfoURL = new Text('speedtest-ip_info_url');
		$speedtestIpInfoURL->setLabel('IPInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->ip_info_url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $speedtestIpInfoToken = new Password('speedtest-ip_info_token');
		$speedtestIpInfoToken->setLabel('IPInfo token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->speedtest->ip_info_token)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

        $this->fields[] = $whatIsMyBrowserAPIURL = new Text('speedtest-what_is_my_browser_api_url');
        $whatIsMyBrowserAPIURL->setLabel('WhatIsMyBrowser API URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => true])
            ->setDefault($this->form->settings->speedtest->what_is_my_browser_api_url)
            ->addValidator(new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true]));

        $this->fields[] = $whatIsMyBrowserAPIKey = new Password('speedtest-what_is_my_browser_api_key');
        $whatIsMyBrowserAPIKey->setLabel('WhatIsMyBrowser API key')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
            ->setDefault($this->form->settings->speedtest->what_is_my_browser_api_key);
	}
}