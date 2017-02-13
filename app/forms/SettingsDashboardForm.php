<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;

/**
 * The form responsible for the dashboard settings.
 *
 * @package Forms
 */
class SettingsDashboardForm extends Form
{
    /**
     * The configuration object containing all the info from config.ini.
     * @var array
     */
    private $_config;

    /**
     * Set the config array (config.ini contents) to private variable.
     *
     * @param array $config     The config array.
     */
    public function __construct($config)
    {
        $this->_config = $config;
        parent::__construct();
    }

    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $this->_action = 'dashboard';

        $devicestateTimeouts = new Text('check-devicestate-interval');
        $devicestateTimeouts->setLabel('Check device state interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->checkDeviceStatesInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $alertTimeout = new Text('alert-timeout');
        $alertTimeout->setLabel('Alert timeout')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->alertTimeout)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $phpSysInfoURL = new Text('phpsysinfo-url');
        $phpSysInfoURL->setLabel('PHPSysInfo URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->phpSysInfoURL);

        $transmissionURL = new Text('transmission-url');
        $transmissionURL->setLabel('Transmission URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->transmissionURL);

        $transmissionUsername = new Text('transmission-username');
        $transmissionUsername->setLabel('Transmission username')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->transmissionUsername);

        $transmissionPassword = new Password('transmission-password');
        $transmissionPassword->setLabel('Transmission password')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->transmissionPassword);

        $transmissionInterval = new Text('transmission-update-interval');
        $transmissionInterval->setLabel('Transmission update interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->transmissionUpdateInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $subsonicURL = new Text('subsonic-url');
        $subsonicURL->setLabel('Subsonic URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->subsonicURL);

        $subsonicUsername = new Text('subsonic-username');
        $subsonicUsername->setLabel('Subsonic username')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->subsonicUsername);

        $subsonicPassword = new Password('subsonic-password');
        $subsonicPassword->setLabel('Subsonic password')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->subsonicPassword);

        $kodiURL = new Text('kodi-url');
        $kodiURL->setLabel('Kodi URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->kodiURL);

        $kodiUsername = new Text('kodi-username');
        $kodiUsername->setLabel('Kodi username')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->kodiUsername);

        $kodiPassword = new Password('kodi-password');
        $kodiPassword->setLabel('Kodi password')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->kodiPassword);

        $rotateMoviesInterval = new Text('rotate-movies-interval');
        $rotateMoviesInterval->setLabel('Rotate movies interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->rotateMoviesInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $rotateEpisodesInterval = new Text('rotate-episodes-interval');
        $rotateEpisodesInterval->setLabel('Rotate episode interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->rotateEpisodesInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $rotateAlbumsInterval = new Text('rotate-albums-interval');
        $rotateAlbumsInterval->setLabel('Rotate albums interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->rotateAlbumsInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $this->add($devicestateTimeouts);
        $this->add($alertTimeout);
        $this->add($phpSysInfoURL);

        $this->add($transmissionURL);
        $this->add($transmissionUsername);
        $this->add($transmissionPassword);
        $this->add($transmissionInterval);

        $this->add($subsonicURL);
        $this->add($subsonicUsername);
        $this->add($subsonicPassword);

        $this->add($kodiURL);
        $this->add($kodiUsername);
        $this->add($kodiPassword);

        $this->add($rotateMoviesInterval);
        $this->add($rotateEpisodesInterval);
        $this->add($rotateAlbumsInterval);
    }

    /**
     * Check if form is valid. If so set the values to the config array.
     *
     * @param   array     $data     The form data posted.
     * @param   object    $entity   The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
    public function IsValid($data = null, $entity = null)
    {
        $valid = parent::IsValid($data, $entity);

        if($valid)
        {
            $this->_config->dashboard->checkDeviceStatesInterval = $data['check-devicestate-interval'];
            $this->_config->dashboard->alertTimeout = $data['alert-timeout'];
            $this->_config->dashboard->phpSysInfoURL = $data['phpsysinfo-url'];

            $this->_config->dashboard->transmissionURL = $data['transmission-url'];
            $this->_config->dashboard->transmissionUsername = $data['transmission-username'];
            $this->_config->dashboard->transmissionPassword = $data['transmission-password'];
            $this->_config->dashboard->transmissionUpdateInterval = $data['transmission-update-interval'];

            $this->_config->dashboard->subsonicURL = $data['subsonic-url'];
            $this->_config->dashboard->subsonicUsername = $data['subsonic-username'];
            $this->_config->dashboard->subsonicPassword = $data['subsonic-password'];

            $this->_config->dashboard->kodiURL = $data['kodi-url'];
            $this->_config->dashboard->kodiUsername = $data['kodi-username'];
            $this->_config->dashboard->kodiPassword = $data['kodi-password'];

            $this->_config->dashboard->rotateMoviesInterval = $data['rotate-movies-interval'];
            $this->_config->dashboard->rotateEpisodesInterval = $data['rotate-episodes-interval'];
            $this->_config->dashboard->rotateAlbumsInterval = $data['rotate-albums-interval'];
            $this->_config->dashboard->rotateAlbumsInterval = $data['rotate-albums-interval'];
        }

        return $valid;
    }
}
