<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;

class SettingsDashboardForm extends Form
{
    private $_config;

    public function __construct($config)
    {
        $this->_config = $config;
        parent::__construct();
    }

    public function initialize()
    {
        $this->_action = 'dashboard';

        $devicestateTimeouts = new Text('check-devicestate-interval');
        $devicestateTimeouts->setLabel('Check device state interval');
        $devicestateTimeouts->setFilters(array('striptags', 'int'));
        $devicestateTimeouts->setAttributes(array('class' => 'form-control'));
        $devicestateTimeouts->setDefault($this->_config->dashboard->checkDeviceStatesInterval);

        $alertTimeout = new Text('alert-timeout');
        $alertTimeout->setLabel('Alert timeout');
        $alertTimeout->setFilters(array('striptags', 'int'));
        $alertTimeout->setAttributes(array('class' => 'form-control'));
        $alertTimeout->setDefault($this->_config->dashboard->alertTimeout);

        $phpSysInfoURL = new Text('phpsysinfo-url');
        $phpSysInfoURL->setLabel('PHPSysInfo URL');
        $phpSysInfoURL->setFilters(array('striptags', 'string'));
        $phpSysInfoURL->setAttributes(array('class' => 'form-control'));
        $phpSysInfoURL->setDefault($this->_config->dashboard->phpSysInfoURL);

        $phpSysInfoVCore = new Text('phpsysinfo-vcore');
        $phpSysInfoVCore->setLabel('PHPSysInfo vcore label');
        $phpSysInfoVCore->setFilters(array('striptags', 'string'));
        $phpSysInfoVCore->setAttributes(array('class' => 'form-control'));
        $phpSysInfoVCore->setDefault($this->_config->dashboard->phpSysInfoVCore);

        $transmissionURL = new Text('transmission-url');
        $transmissionURL->setLabel('Transmission URL');
        $transmissionURL->setFilters(array('striptags', 'string'));
        $transmissionURL->setAttributes(array('class' => 'form-control'));
        $transmissionURL->setDefault($this->_config->dashboard->transmissionURL);

        $transmissionUsername = new Text('transmission-username');
        $transmissionUsername->setLabel('Transmission username');
        $transmissionUsername->setFilters(array('striptags', 'string'));
        $transmissionUsername->setAttributes(array('class' => 'form-control'));
        $transmissionUsername->setDefault($this->_config->dashboard->transmissionUsername);

        $transmissionPassword = new Password('transmission-password');
        $transmissionPassword->setLabel('Transmission password');
        $transmissionPassword->setFilters(array('striptags', 'string'));
        $transmissionPassword->setAttributes(array('class' => 'form-control'));
        $transmissionPassword->setDefault($this->_config->dashboard->transmissionPassword);

        $transmissionInterval = new Text('transmission-update-interval');
        $transmissionInterval->setLabel('Transmission update inteval');
        $transmissionInterval->setFilters(array('striptags', 'int'));
        $transmissionInterval->setAttributes(array('class' => 'form-control'));
        $transmissionInterval->setDefault($this->_config->dashboard->transmissionUpdateInterval);

        $this->add($devicestateTimeouts);
        $this->add($alertTimeout);
        $this->add($phpSysInfoURL);
        $this->add($phpSysInfoVCore);
        $this->add($transmissionURL);
        $this->add($transmissionUsername);
        $this->add($transmissionPassword);
        $this->add($transmissionInterval);
    }

    public function IsValid($data)
    {
        $valid = parent::IsValid($data);

        if($valid)
        {
            $this->_config->dashboard->checkDeviceStatesTimeout = $data['check-devicestate-timeouts'];
            $this->_config->dashboard->alertTimeout = $data['alert-timeout'];
            $this->_config->dashboard->phpSysInfoURL = $data['phpsysinfo-url'];
            $this->_config->dashboard->phpSysInfoVCore = $data['phpsysinfo-vcore'];
            $this->_config->dashboard->transmissionURL = $data['transmission-url'];
            $this->_config->dashboard->transmissionUsername = $data['transmission-username'];
            $this->_config->dashboard->transmissionPassword = $data['transmission-password'];
            $this->_config->dashboard->transmissionUpdateInterval = $data['transmission-update-interval'];
        }

        return $valid;
    }
}
