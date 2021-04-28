<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class SpeedtestFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$speedtestEnabled = new Check('speedtest-enabled');
		$speedtestEnabled->setLabel('Enabled');
		$speedtestEnabled->setAttributes([
			'checked' => $form->_config->speedtest->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->_config->speedtest->test_order);

		$speedtestUpTime = new Numeric('speedtest-time-ul');
		$speedtestUpTime->setLabel('Upload time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->speedtest->time_dl)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$speedtestDownloadTime = new Numeric('speedtest-time-dl');
		$speedtestDownloadTime->setLabel('Download time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->speedtest->time_dl)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$speedtestGetIP = new Check('speedtest-get-ispip');
		$speedtestGetIP->setLabel('Get ISP IP');
		$speedtestGetIP->setAttributes([
			'checked' => $form->_config->speedtest->getIp_ispInfo == '1' ? 'checked' : null,
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
			->setDefault($form->_config->speedtest->getIp_ispInfo_distance);

		$speedtestTelemetry = new Select('speedtest-telemetry', ['off' => 'Off', 'basic' => 'Basic', 'full' => 'Full']);
		$speedtestTelemetry->setLabel('Telemetry')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->_config->speedtest->telemetry);

		$speedtestIpInfoURL = new Text('speedtest-ipinfo-url');
		$speedtestIpInfoURL->setLabel('IPInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->_config->speedtest->ipInfoUrl);

		$speedtestIpInfoToken = new Password('speedtest-ipinfo-token');
		$speedtestIpInfoToken->setLabel('IPInfo token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->speedtest->ipInfoToken);

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