<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
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

        $devicestateTimeouts = new Text('check-devicestate-timeouts');
        $devicestateTimeouts->setLabel('Check device state timeouts');
        $devicestateTimeouts->setFilters(array('striptags', 'int'));
        $devicestateTimeouts->setAttributes(array('class' => 'form-control'));
        $devicestateTimeouts->setDefault($this->_config->dashboard->checkDeviceStatesTimeout);

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

        $this->add($devicestateTimeouts);
        $this->add($alertTimeout);
        $this->add($phpSysInfoURL);
        $this->add($phpSysInfoVCore);
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
        }

        return $valid;
    }
}