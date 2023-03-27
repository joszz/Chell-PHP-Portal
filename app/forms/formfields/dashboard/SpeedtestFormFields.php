<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

/**
 * The formfields for the Speedtest plugin
 *
 * @package Formfields
 */
class SpeedtestFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('speedtest-enabled', [
			'fieldset' => 'Speedtest',
			'checked' => $this->form->settings->speedtest->enabled->value == '1' ? 'checked' : null,
		]);

		$this->fields[] = $speedtestTestOrder = new Text('speedtest-test_order');
		$speedtestTestOrder->setLabel('Test order')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->test_order->value ?? 'IPDU')
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$this->fields[] = $speedtestUpTime = new Numeric('speedtest-time_upload');
		$speedtestUpTime->setLabel('Upload time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->time_upload->value ?? 10)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled'])
			]);

		$this->fields[] = $speedtestDownloadTime = new Numeric('speedtest-time_download');
		$speedtestDownloadTime->setLabel('Download time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->time_download->value ?? 10)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled'])
			]);

		$this->fields[] = $speedtestGetIP = new Check('speedtest-get_isp_info');
		$speedtestGetIP->setLabel('Get ISP IP');
		$speedtestGetIP->setAttributes([
			'value' => '1',
			'checked' => $this->form->settings->speedtest->get_isp_info->value == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small'
		]);

		$this->fields[] = $speedtestISPInfo = new Select('speedtest-get_isp_distance', ['km' => 'Kilometers', 'mi' => 'Miles']);
		$speedtestISPInfo->setLabel('Distance units')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->get_isp_distance->value)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$this->fields[] = $speedtestTelemetry = new Select('speedtest-telemetry', ['off' => 'Off', 'basic' => 'Basic', 'full' => 'Full']);
		$speedtestTelemetry->setLabel('Telemetry')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->telemetry->value)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$this->fields[] = $speedtestIpInfoURL = new Text('speedtest-ip_info_url');
		$speedtestIpInfoURL->setLabel('IPInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->ip_info_url->value ?? 'https://ipinfo.io/')
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);

		$this->fields[] = $speedtestIpInfoToken = new Password('speedtest-ip_info_token');
		$speedtestIpInfoToken->setLabel('IPInfo token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->speedtest->ip_info_token->value)
			->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'speedtest-enabled']));

        $this->fields[] = $whatIsMyBrowserAPIURL = new Text('speedtest-what_is_my_browser_api_url');
        $whatIsMyBrowserAPIURL->setLabel('WhatIsMyBrowser API URL')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->speedtest->what_is_my_browser_api_url->value ?? 'https://api.whatismybrowser.com/api/v2/')
            ->addValidator(new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true]));

        $this->fields[] = $whatIsMyBrowserAPIKey = new Password('speedtest-what_is_my_browser_api_key');
        $whatIsMyBrowserAPIKey->setLabel('WhatIsMyBrowser API key')
            ->setFilters(['striptags', 'string'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->form->settings->speedtest->what_is_my_browser_api_key->value);
	}
}