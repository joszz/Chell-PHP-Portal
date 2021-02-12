<?php

namespace Chell\Forms;

use Chell\Models\Devices;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;

use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Regex;

/**
 * The form responsible for the dashboard settings.
 *
 * @package Forms
 */
class SettingsDashboardForm extends SettingsBaseForm
{
	/**
     * Add all fields to the form and set form specific attributes.
     */
	public function initialize()
	{
		$this->setAction($this->_config->application->baseUri . 'settings/dashboard#dashboard');

		$devicestateTimeouts = new Numeric('check-devicestate-interval');
		$devicestateTimeouts->setLabel('Check device state interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->_config->dashboard->checkDeviceStatesInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));
		$this->add($devicestateTimeouts);

		$this->setPHPSysInfoFields();
		$this->setrCPUFields();
		$this->setTransmissionFields();
		$this->setSubsonicFields();
		$this->setKodiFields();
		$this->setSickrageFields();
		$this->setCouchpotatoFields();
		$this->setHyperVAdminFields();
		$this->setMotionFields();
		$this->setSpeedtestFields();
		$this->setOpcacheFields();
		$this->setPiHoleFields();
		$this->setYoulessFields();
		$this->setSnmpFields();
		$this->setVerisureFields();
	}

	/**
     * Adds rCPU fields to the form.
     */
	private function setrCPUFields()
	{
		$rCpuEnabled = new Check('rcpu-enabled');
		$rCpuEnabled->setLabel('Enabled');
		$rCpuEnabled->setAttributes([
			'checked' => $this->_config->rcpu->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'rCPU',
		]);

		$rCpuURL = new Text('rcpu-url');
		$rCpuURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->rcpu->URL);

		$this->add($rCpuEnabled);
		$this->add($rCpuURL);
	}

	/**
     * Adds PHPSysInfo fields to the form.
     */
	private function setPHPSysInfoFields()
	{
		$phpSysInfoEnabled = new Check('phpsysinfo-enabled');
		$phpSysInfoEnabled->setLabel('Enabled');
		$phpSysInfoEnabled->setAttributes([
			'checked' => $this->_config->phpsysinfo->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'PHPSysInfo'
		]);

		$phpSysInfoURL = new Text('phpsysinfo-url');
		$phpSysInfoURL->setLabel('PHPSysInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->phpsysinfo->URL)
			->addValidators([new PresenceOf([])]);

		$phpSysInfoUsername = new Text('phpsysinfo-username');
		$phpSysInfoUsername->setLabel('PHPSysInfo username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->phpsysinfo->username);

		$phpSysInfoPassword = new Password('phpsysinfo-password');
		$phpSysInfoPassword->setLabel('PHPSysInfo password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => 'end'])
			->setDefault($this->_config->phpsysinfo->password);

		$this->add($phpSysInfoEnabled);
		$this->add($phpSysInfoURL);
		$this->add($phpSysInfoUsername);
		$this->add($phpSysInfoPassword);
	}

	/**
     * Adds Transmission fields to the form.
     */
	private function setTransmissionFields()
	{
		$transmissionEnabled = new Check('transmission-enabled');
		$transmissionEnabled->setLabel('Enabled');
		$transmissionEnabled->setAttributes([
			'checked' => $this->_config->transmission->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Transmission'
		]);

		$transmissionURL = new Text('transmission-url');
		$transmissionURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->transmission->URL);

		$transmissionUsername = new Text('transmission-username');
		$transmissionUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->transmission->username);

		$transmissionPassword = new Password('transmission-password');
		$transmissionPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($this->_config->transmission->password);

		$transmissionInterval = new Numeric('transmission-update-interval');
		$transmissionInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->transmission->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$this->add($transmissionEnabled);
		$this->add($transmissionURL);
		$this->add($transmissionUsername);
		$this->add($transmissionPassword);
		$this->add($transmissionInterval);
	}

	/**
     * Adds Subsonic fields to the form.
     */
	private function setSubsonicFields()
	{
		$subsonicEnabled = new Check('subsonic-enabled');
		$subsonicEnabled->setLabel('Enabled');
		$subsonicEnabled->setAttributes([
			'checked' => $this->_config->subsonic->enabled == '1' ? 'checked' : null,
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
			->setDefault($this->_config->subsonic->URL);

		$subsonicUsername = new Text('subsonic-username');
		$subsonicUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->subsonic->username);

		$subsonicPassword = new Password('subsonic-password');
		$subsonicPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end', 'autocomplete' => 'new-password'])
			->setDefault($this->_config->subsonic->password);

		$this->add($subsonicEnabled);
		$this->add($subsonicURL);
		$this->add($subsonicUsername);
		$this->add($subsonicPassword);
	}

	/**
     * Adds Kodi fields to the form.
     */
	private function setKodiFields()
	{
		$kodiEnabled = new Check('kodi-enabled');
		$kodiEnabled->setLabel('Enabled');
		$kodiEnabled->setAttributes([
			'checked' => $this->_config->kodi->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Kodi'
		]);

		$kodiURL = new Text('kodi-url');
		$kodiURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->kodi->URL);

		$kodiUsername = new Text('kodi-username');
		$kodiUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->kodi->username);

		$kodiPassword = new Password('kodi-password');
		$kodiPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($this->_config->kodi->password);

		$rotateMoviesInterval = new Numeric('kodi-rotate-movies-interval');
		$rotateMoviesInterval->setLabel('Rotate movies interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->kodi->rotateMoviesInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$rotateEpisodesInterval = new Numeric('kodi-rotate-episodes-interval');
		$rotateEpisodesInterval->setLabel('Rotate episode interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->kodi->rotateEpisodesInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$rotateAlbumsInterval = new Numeric('kodi-rotate-albums-interval');
		$rotateAlbumsInterval->setLabel('Rotate albums interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->kodi->rotateAlbumsInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$this->add($kodiEnabled);
		$this->add($kodiURL);
		$this->add($kodiUsername);
		$this->add($kodiPassword);
		$this->add($rotateMoviesInterval);
		$this->add($rotateEpisodesInterval);
		$this->add($rotateAlbumsInterval);
	}

	/**
     * Adds Sickrage fields to the form.
     */
	private function setSickrageFields()
	{
		$sickrageEnabled = new Check('sickrage-enabled');
		$sickrageEnabled->setLabel('Enabled');
		$sickrageEnabled->setAttributes([
			'checked' => $this->_config->sickrage->enabled == '1' ? 'checked' : null,
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
			->setDefault($this->_config->sickrage->URL);

		$sickrageAPIKey = new Password('sickrage-apikey');
		$sickrageAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->sickrage->APIKey);

		$this->add($sickrageEnabled);
		$this->add($sickrageURL);
		$this->add($sickrageAPIKey);
	}

	/**
     * Adds Couchpotato fields to the form.
     */
	private function setCouchpotatoFields()
	{
		$couchpotatoEnabled = new Check('couchpotato-enabled');
		$couchpotatoEnabled->setLabel('Enabled');
		$couchpotatoEnabled->setAttributes([
			'checked' => $this->_config->couchpotato->enabled == '1' ? 'checked' : null,
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
			->setDefault($this->_config->couchpotato->URL);

		$couchpotatoAPIKey = new Password('couchpotato-apikey');
		$couchpotatoAPIKey->setLabel('API key')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->couchpotato->APIKey);

		$rotateInterval = new Numeric('couchpotato-rotate-interval');
		$rotateInterval->setLabel('Rotate interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->couchpotato->rotateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$this->add($couchpotatoEnabled);
		$this->add($couchpotatoURL);
		$this->add($couchpotatoAPIKey);
		$this->add($rotateInterval);
	}

	/**
     * Adds HyperVAdmin fields to the form.
     */
	private function setHyperVAdminFields()
	{
		$hyperVAdminEnabled = new Check('hypervadmin-enabled');
		$hyperVAdminEnabled->setLabel('Enabled');
		$hyperVAdminEnabled->setAttributes([
			'checked' => $this->_config->hypervadmin->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'HyperVAdmin'
		]);

		$hyperVAdminURL = new Text('hypervadmin-url');
		$hyperVAdminURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->hypervadmin->URL);

		$hyperVAdminUsername = new Text('hypervadmin-username');
		$hyperVAdminUsername->setLabel('Username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->hypervadmin->username);

		$hyperVAdminPassword = new Password('hypervadmin-password');
		$hyperVAdminPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true, 'autocomplete' => 'new-password'])
			->setDefault($this->_config->hypervadmin->password);

		$deviceOptions[0] = 'Please select';
		foreach(Devices::Find() as $device) {
			$deviceOptions[$device->id] = $device->name;
		}
		$hyperVAdminDevice = new Select('hypervadmin-device', $deviceOptions, ['fieldset' => 'end']);
		$hyperVAdminDevice->setLabel('Host');
		$hyperVAdminDevice->setDefault($this->_config->hypervadmin->device);

		$this->add($hyperVAdminEnabled);
		$this->add($hyperVAdminURL);
		$this->add($hyperVAdminUsername);
		$this->add($hyperVAdminPassword);
		$this->add($hyperVAdminDevice);
	}

	/**
     * Adds Motion fields to the form.
     */
	private function setMotionFields()
	{
		$motionEnabled = new Check('motion-enabled');
		$motionEnabled->setLabel('Enabled');
		$motionEnabled->setAttributes([
			'checked' => $this->_config->motion->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Motion'
		]);

		$motionURL = new Text('motion-url');
		$motionURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->motion->URL);

		$motionPicturePath = new Text('motion-picturepath');
		$motionPicturePath->setLabel('Picture path')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->motion->picturePath);

		$motionInterval = new Numeric('motion-update-interval');
		$motionInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->motion->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));


		$this->add($motionEnabled);
		$this->add($motionURL);
		$this->add($motionPicturePath);
		$this->add($motionInterval);
	}

	/**
     * Adds Speedtest fields to the form.
     */
	private function setSpeedtestFields()
	{
		$speedtestEnabled = new Check('speedtest-enabled');
		$speedtestEnabled->setLabel('Enabled');
		$speedtestEnabled->setAttributes([
			'checked' => $this->_config->speedtest->enabled == '1' ? 'checked' : null,
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
			->setDefault($this->_config->speedtest->test_order);

		$speedtestUpTime = new Numeric('speedtest-time-ul');
		$speedtestUpTime->setLabel('Upload time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->speedtest->time_dl)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$speedtestDownloadTime = new Numeric('speedtest-time-dl');
		$speedtestDownloadTime->setLabel('Download time')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->speedtest->time_dl)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$speedtestGetIP = new Check('speedtest-get-ispip');
		$speedtestGetIP->setLabel('Get ISP IP');
		$speedtestGetIP->setAttributes([
			'checked' => $this->_config->speedtest->getIp_ispInfo == '1' ? 'checked' : null,
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
			->setDefault($this->_config->speedtest->getIp_ispInfo_distance);

		$speedtestTelemetry = new Select('speedtest-telemetry', ['off' => 'Off', 'basic' => 'Basic', 'full' => 'Full']);
		$speedtestTelemetry->setLabel('Telemetry')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->_config->speedtest->telemetry);

		$speedtestIpInfoURL = new Text('speedtest-ipinfo-url');
		$speedtestIpInfoURL->setLabel('IPInfo URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->_config->speedtest->ipInfoUrl);

		$speedtestIpInfoToken = new Password('speedtest-ipinfo-token');
		$speedtestIpInfoToken->setLabel('IPInfo token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->speedtest->ipInfoToken);

		$this->add($speedtestEnabled);
		$this->add($speedtestTestOrder);
		$this->add($speedtestUpTime);
		$this->add($speedtestDownloadTime);
		$this->add($speedtestGetIP);
		$this->add($speedtestISPInfo);
		$this->add($speedtestTelemetry);
		$this->add($speedtestIpInfoURL);
		$this->add($speedtestIpInfoToken);
	}

	/**
     * Adds Opcache fields to the form.
     */
	private function setOpcacheFields()
	{
		$opcacheEnabled = new Check('opcache-enabled');
		$opcacheEnabled->setLabel('Enabled');
		$opcacheEnabled->setAttributes([
			'checked' => $this->_config->opcache->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Opcache'
		]);

		$opcacheHidden = new Hidden('opcache-hidden');
		$opcacheHidden->setLabel('');
		$opcacheHidden->setAttributes(['fieldset' => 'end']);

		$this->add($opcacheEnabled);
		$this->add($opcacheHidden);
	}

	/**
     * Adds PiHole fields to the form.
     */
	private function setPiHoleFields()
	{
		$piholeEnabled = new Check('pihole-enabled');
		$piholeEnabled->setLabel('Enabled');
		$piholeEnabled->setAttributes([
			'checked' => $this->_config->pihole->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Pi-hole'
		]);

		$piholeURL = new Text('pihole-url');
		$piholeURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->pihole->URL);

		$this->add($piholeEnabled);
		$this->add($piholeURL);
	}

	/**
     * Adds Youless fields to the form.
     */
	private function setYoulessFields()
	{
		$youlessEnabled = new Check('youless-enabled');
		$youlessEnabled->setLabel('Enabled');
		$youlessEnabled->setAttributes([
			'checked' => $this->_config->youless->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Youless'
		]);

		$youlessURL = new Text('youless-url');
		$youlessURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->youless->URL);

		$youlessPassword = new Password('youless-password');
		$youlessPassword->setLabel('YouLess password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($this->_config->youless->password);

		$youlessInterval = new Numeric('youless-update-interval');
		$youlessInterval->setLabel('YouLess interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->youless->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$youlessPrimaryThreshold = new Numeric('youless-primary-threshold');
		$youlessPrimaryThreshold->setLabel('YouLess primary threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->youless->primaryThreshold)
			->addValidator(new Regex(['pattern' => '/^[0-9]+$/', 'message' => 'Not a number']));

		$youlessWarnThreshold = new Numeric('youless-warn-threshold');
		$youlessWarnThreshold->setLabel('YouLess warn threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->youless->warnThreshold)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$youlessDangerThreshold = new Numeric('youless-danger-threshold');
		$youlessDangerThreshold->setLabel('YouLess danger threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->youless->dangerThreshold)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$this->add($youlessEnabled);
		$this->add($youlessURL);
		$this->add($youlessPassword);
		$this->add($youlessInterval);
		$this->add($youlessPrimaryThreshold);
		$this->add($youlessWarnThreshold);
		$this->add($youlessDangerThreshold);
	}

    /**
     * Adds SNMP fields to the form.
     */
	private function setSnmpFields()
	{
		$snmpEnabled = new Check('snmp-enabled');
		$snmpEnabled->setLabel('Enabled');
		$snmpEnabled->setAttributes([
			'checked' => $this->_config->snmp->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'SNMP'
		]);

		$snmpInterval = new Numeric('snmp-update-interval');
		$snmpInterval->setLabel('SNMP interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->snmp->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$this->add($snmpEnabled);
		$this->add($snmpInterval);
	}


    /**
     * Adds Verisure fields to the form.
     */
	private function setVerisureFields()
	{
		$verisureEnabled = new Check('verisure-enabled');
		$verisureEnabled->setLabel('Enabled');
		$verisureEnabled->setAttributes([
			'checked' => $this->_config->verisure->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Verisure'
		]);

		$verisureInterval = new Numeric('verisure-update-interval');
		$verisureInterval->setLabel('Verisure interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->verisure->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

        $verisureURL = new Text('verisure-url');
		$verisureURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->_config->verisure->URL);

		$verisureUsername = new Text('verisure-username');
		$verisureUsername->setLabel('Verisure username')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->_config->verisure->username);

		$verisurePassword = new Password('verisure-password');
		$verisurePassword->setLabel('Verisure password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($this->_config->verisure->password);

        $verisurePin = new Password('verisure-pin');
		$verisurePin->setLabel('Verisure pin')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->_config->verisure->securityCode);

		$this->add($verisureEnabled);
		$this->add($verisureInterval);
		$this->add($verisureURL);
		$this->add($verisureUsername);
		$this->add($verisurePassword);
		$this->add($verisurePin);
	}

	/**
     * Check if form is valid. If so set the values to the config array.
     *
     * @param   array     $data     The form data posted.
     * @param   object    $entity   The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
	public function IsValid($data = null, $entity = null) : bool
	{
		$valid = parent::IsValid($data, $entity);

		if ($valid)
		{
			$this->_config->dashboard->checkDeviceStatesInterval = $data['check-devicestate-interval'];

			$this->_config->rcpu->enabled = isset($data['rcpu-enabled']) && $data['rcpu-enabled'] == 'on' ? '1' : '0';
			$this->_config->rcpu->URL = $data['rcpu-url'];

			$this->_config->phpsysinfo->enabled = isset($data['phpsysinfo-enabled']) && $data['phpsysinfo-enabled'] == 'on' ? '1' : '0';
			$this->_config->phpsysinfo->URL = $data['phpsysinfo-url'];
			$this->_config->phpsysinfo->username = $data['phpsysinfo-username'];
			$this->_config->phpsysinfo->password = $data['phpsysinfo-password'];

			$this->_config->transmission->enabled = isset($data['transmission-enabled']) && $data['transmission-enabled'] == 'on' ? '1' : '0';
			$this->_config->transmission->URL = $data['transmission-url'];
			$this->_config->transmission->username = $data['transmission-username'];
			$this->_config->transmission->password = $data['transmission-password'];
			$this->_config->transmission->updateInterval = $data['transmission-update-interval'];

			$this->_config->subsonic->enabled = isset($data['subsonic-enabled']) && $data['subsonic-enabled'] == 'on' ? '1' : '0';
			$this->_config->subsonic->URL = $data['subsonic-url'];
			$this->_config->subsonic->username = $data['subsonic-username'];
			$this->_config->subsonic->password = $data['subsonic-password'];

			$this->_config->kodi->enabled = isset($data['kodi-enabled']) && $data['kodi-enabled'] == 'on' ? '1' : '0';
			$this->_config->kodi->URL = $data['kodi-url'];
			$this->_config->kodi->username = $data['kodi-username'];
			$this->_config->kodi->password = $data['kodi-password'];
			$this->_config->kodi->rotateMoviesInterval = $data['kodi-rotate-movies-interval'];
			$this->_config->kodi->rotateEpisodesInterval = $data['kodi-rotate-episodes-interval'];
			$this->_config->kodi->rotateAlbumsInterval = $data['kodi-rotate-albums-interval'];

			$this->_config->sickrage->enabled = isset($data['sickrage-enabled']) && $data['sickrage-enabled'] == 'on' ? '1' : '0';
			$this->_config->sickrage->URL = $data['sickrage-url'];
			$this->_config->sickrage->APIKey = $data['sickrage-apikey'];

			$this->_config->couchpotato->enabled = isset($data['couchpotato-enabled']) && $data['couchpotato-enabled'] == 'on' ? '1' : '0';
			$this->_config->couchpotato->URL = $data['couchpotato-url'];
			$this->_config->couchpotato->APIKey = $data['couchpotato-apikey'];
			$this->_config->couchpotato->rotateInterval = $data['couchpotato-rotate-interval'];

			$this->_config->hypervadmin->enabled = isset($data['hypervadmin-enabled']) && $data['hypervadmin-enabled'] == 'on' ? '1' : '0';
			$this->_config->hypervadmin->URL = $data['hypervadmin-url'];
			$this->_config->hypervadmin->username = $data['hypervadmin-username'];
			$this->_config->hypervadmin->password = $data['hypervadmin-password'];
			$this->_config->hypervadmin->device = $data['hypervadmin-device'];

			$this->_config->motion->enabled = isset($data['motion-enabled']) && $data['motion-enabled'] == 'on' ? '1' : '0';
			$this->_config->motion->URL = $data['motion-url'];
			$this->_config->motion->picturePath = $data['motion-picturepath'];
			$this->_config->motion->updateInterval = $data['motion-update-interval'];

			$this->_config->speedtest->enabled = isset($data['speedtest-enabled']) && $data['speedtest-enabled'] == 'on' ? '1' : '0';
			$this->_config->speedtest->test_order = $data['speedtest-test-order'];
			$this->_config->speedtest->time_ul = $data['speedtest-time-ul'];
			$this->_config->speedtest->time_dl = $data['speedtest-time-dl'];
			$this->_config->speedtest->getIp_ispInfo = $data['speedtest-get-ispip'];
			$this->_config->speedtest->getIp_ispInfo_distance = $data['speedtest-isp-info-distance'];
			$this->_config->speedtest->ipInfoUrl = $data['speedtest-ipinfo-url'];
			$this->_config->speedtest->ipInfoToken = $data['speedtest-ipinfo-token'];

			$this->_config->opcache->enabled = isset($data['opcache-enabled']) && $data['opcache-enabled'] == 'on' ? '1' : '0';

			$this->_config->pihole->enabled = isset($data['pihole-enabled']) && $data['pihole-enabled'] == 'on' ? '1' : '0';
			$this->_config->pihole->URL = $data['pihole-url'];

			$this->_config->youless->enabled = isset($data['youless-enabled']) && $data['youless-enabled'] == 'on' ? '1' : '0';
			$this->_config->youless->URL = $data['youless-url'];
			$this->_config->youless->password = $data['youless-password'];
			$this->_config->youless->updateInterval = $data['youless-update-interval'];
			$this->_config->youless->primaryThreshold = $data['youless-primary-threshold'];
			$this->_config->youless->warnThreshold = $data['youless-warn-threshold'];
			$this->_config->youless->dangerThreshold = $data['youless-danger-threshold'];

			$this->_config->snmp->enabled = isset($data['snmp-enabled']) && $data['snmp-enabled'] == 'on' ? '1' : '0';
			$this->_config->snmp->updateInterval = $data['snmp-update-interval'];

            $this->_config->verisure->enabled = isset($data['verisure-enabled']) && $data['verisure-enabled'] == 'on' ? '1' : '0';
			$this->_config->verisure->updateInterval = $data['verisure-update-interval'];
			$this->_config->verisure->URL = $data['verisure-url'];
			$this->_config->verisure->username = $data['verisure-username'];
			$this->_config->verisure->password = $data['verisure-password'];
			$this->_config->verisure->securityCode = $data['verisure-pin'];
		}

		return $valid;
	}
}
