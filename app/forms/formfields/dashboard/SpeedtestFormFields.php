<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class SpeedtestFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$speedtestEnabled = new Check('speedtest-enabled');
		$speedtestEnabled->setLabel('Enabled');
		$speedtestEnabled->setAttributes([
			'checked' => $form->config->speedtest->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->config->speedtest->test_order)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestUpTime = new Numeric('speedtest-time-ul');
		$speedtestUpTime->setLabel('Upload time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->speedtest->time_dl)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestDownloadTime = new Numeric('speedtest-time-dl');
		$speedtestDownloadTime->setLabel('Download time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->speedtest->time_dl)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestGetIP = new Check('speedtest-get-ispip');
		$speedtestGetIP->setLabel('Get ISP IP');
		$speedtestGetIP->setAttributes([
			'checked' => $form->config->speedtest->getIp_ispInfo == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => true
		])
		->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestISPInfo = new Select('speedtest-isp-info-distance', ['km' => 'Kilometers', 'mi' => 'Miles']);
		$speedtestISPInfo->setLabel('Distance units')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->speedtest->getIp_ispInfo_distance)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestTelemetry = new Select('speedtest-telemetry', ['off' => 'Off', 'basic' => 'Basic', 'full' => 'Full']);
		$speedtestTelemetry->setLabel('Telemetry')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->config->speedtest->telemetry)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestIpInfoURL = new Text('speedtest-ipinfo-url');
		$speedtestIpInfoURL->setLabel('IPInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->config->speedtest->ipInfoUrl)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$speedtestIpInfoToken = new Password('speedtest-ipinfo-token');
		$speedtestIpInfoToken->setLabel('IPInfo token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->speedtest->ipInfoToken)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'speedtest-enabled']));

		$form->add($speedtestEnabled);
		$form->add($speedtestTestOrder);
		$form->add($speedtestUpTime);
		$form->add($speedtestDownloadTime);
		$form->add($speedtestGetIP);
		$form->add($speedtestISPInfo);
		$form->add($speedtestTelemetry);
		$form->add($speedtestIpInfoURL);
		$form->add($speedtestIpInfoToken);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
		$config->speedtest->enabled = isset($data['speedtest-enabled']) && $data['speedtest-enabled'] == 'on' ? '1' : '0';
		$config->speedtest->test_order = $data['speedtest-test-order'];
		$config->speedtest->time_ul = $data['speedtest-time-ul'];
		$config->speedtest->time_dl = $data['speedtest-time-dl'];
		$config->speedtest->getIp_ispInfo = $data['speedtest-get-ispip'];
		$config->speedtest->getIp_ispInfo_distance = $data['speedtest-isp-info-distance'];
		$config->speedtest->ipInfoUrl = $data['speedtest-ipinfo-url'];
		$config->speedtest->ipInfoToken = $data['speedtest-ipinfo-token'];
    }
}