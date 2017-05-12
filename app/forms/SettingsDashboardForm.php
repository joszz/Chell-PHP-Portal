<?php

namespace Chell\Forms;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
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
        $this->_action = 'dashboard';

        $devicestateTimeouts = new Numeric('check-devicestate-interval');
        $devicestateTimeouts->setLabel('Check device state interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->dashboard->checkDeviceStatesInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));
        $this->add($devicestateTimeouts);

        $this->setPHPSysInfoFields();
        $this->setrCPUFields();
        $this->setTransmissionFields();
        $this->setSubsonicFields();
        $this->setKodiFields();
        $this->setSickrageFields();
        $this->setCouchpotatoFields();
    }

    /**
     * Adds rCPU fields to the form.
     */
    private function setrCPUFields()
    {
        $rCpuEnabled = new Check('rcpu-enabled');
        $rCpuEnabled->setLabel('Enabled');
        $rCpuEnabled->setAttributes(array(
            'checked' => $this->_config->rcpu->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'rCPU'
        ));

        $rCpuURL = new Text('rcpu-url');
        $rCpuURL->setLabel('rCPU URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => 'end'))
            ->setDefault($this->_config->rcpu->URL);

        $this->add($rCpuEnabled);
        $this->add($rCpuURL);
    }

    /**
     * Adds PHPSysInfo fields to the form.
     */
    private function setPHPSysInfoFields()
    {
        $phpSysInfoURL = new Text('phpsysinfo-url');
        $phpSysInfoURL->setLabel('PHPSysInfo URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->phpsysinfo->URL);

        $phpSysInfoUsername = new Text('phpsysinfo-username');
        $phpSysInfoUsername->setLabel('PHPSysInfo username')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->phpsysinfo->username);

        $phpSysInfoPassword = new Password('phpsysinfo-password');
        $phpSysInfoPassword->setLabel('PHPSysInfo password')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->phpsysinfo->password);

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
        $transmissionEnabled->setAttributes(array(
            'checked' => $this->_config->transmission->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Transmission'
        ));

        $transmissionURL = new Text('transmission-url');
        $transmissionURL->setLabel('URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->transmission->URL);

        $transmissionUsername = new Text('transmission-username');
        $transmissionUsername->setLabel('Username')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->transmission->username);

        $transmissionPassword = new Password('transmission-password');
        $transmissionPassword->setLabel('Password')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->transmission->password);

        $transmissionInterval = new Numeric('transmission-update-interval');
        $transmissionInterval->setLabel('Update interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => 'end'))
            ->setDefault($this->_config->transmission->updateInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

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
        $subsonicEnabled->setAttributes(array(
            'checked' => $this->_config->subsonic->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Subsonic'
        ));

        $subsonicURL = new Text('subsonic-url');
        $subsonicURL->setLabel('URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->subsonic->URL);

        $subsonicUsername = new Text('subsonic-username');
        $subsonicUsername->setLabel('Username')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->subsonic->username);

        $subsonicPassword = new Password('subsonic-password');
        $subsonicPassword->setLabel('Password')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => 'end'))
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
        $kodiEnabled->setAttributes(array(
            'checked' => $this->_config->kodi->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Kodi'
        ));

        $kodiURL = new Text('kodi-url');
        $kodiURL->setLabel('URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->kodi->URL);

        $kodiUsername = new Text('kodi-username');
        $kodiUsername->setLabel('Username')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->kodi->username);

        $kodiPassword = new Password('kodi-password');
        $kodiPassword->setLabel('Password')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->kodi->password);

        $rotateMoviesInterval = new Numeric('rotate-movies-interval');
        $rotateMoviesInterval->setLabel('Rotate movies interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->kodi->rotateMoviesInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $rotateEpisodesInterval = new Numeric('rotate-episodes-interval');
        $rotateEpisodesInterval->setLabel('Rotate episode interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->kodi->rotateEpisodesInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $rotateAlbumsInterval = new Numeric('rotate-albums-interval');
        $rotateAlbumsInterval->setLabel('Rotate albums interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => 'end'))
            ->setDefault($this->_config->kodi->rotateAlbumsInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

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
        $sickrageEnabled->setAttributes(array(
            'checked' => $this->_config->sickrage->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Sickrage'
        ));

        $sickrageURL = new Text('sickrage-url');
        $sickrageURL->setLabel('URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->sickrage->URL);

        $sickrageAPIKey = new Text('sickrage-apikey');
        $sickrageAPIKey->setLabel('API key')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => 'end'))
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
        $couchpotatoEnabled->setAttributes(array(
            'checked' => $this->_config->couchpotato->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Couchpotato'
        ));

        $couchpotatoURL = new Text('couchpotato-url');
        $couchpotatoURL->setLabel('URL')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->couchpotato->URL);

        $couchpotatoAPIKey = new Text('couchpotato-apikey');
        $couchpotatoAPIKey->setLabel('API key')
            ->setFilters(array('striptags', 'string'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => true))
            ->setDefault($this->_config->couchpotato->APIKey);

        $rotateInterval = new Numeric('couchpotato-rotate-interval');
        $rotateInterval->setLabel('Rotate interval')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control', 'fieldset' => 'end'))
            ->setDefault($this->_config->couchpotato->rotateInterval)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $this->add($couchpotatoEnabled);
        $this->add($couchpotatoURL);
        $this->add($couchpotatoAPIKey);
        $this->add($rotateInterval);
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

            $this->_config->rcpu->enabled = $data['rcpu-enabled'] == 'on' ? '1' : '0';
            $this->_config->rcpu->URL = $data['rcpu-url'];

            $this->_config->phpsysinfo->URL = $data['phpsysinfo-url'];
            $this->_config->phpsysinfo->username = $data['phpsysinfo-username'];
            $this->_config->phpsysinfo->password = $data['phpsysinfo-password'];

            $this->_config->transmission->enabled = $data['transmission-enabled'] == 'on' ? '1' : '0';
            $this->_config->transmission->URL = $data['transmission-url'];
            $this->_config->transmission->username = $data['transmission-username'];
            $this->_config->transmission->password = $data['transmission-password'];
            $this->_config->transmission->updateInterval = $data['transmission-update-interval'];

            $this->_config->subsonic->enabled = $data['subsonic-enabled'] == 'on' ? '1' : '0';
            $this->_config->subsonic->URL = $data['subsonic-url'];
            $this->_config->subsonic->username = $data['subsonic-username'];
            $this->_config->subsonic->password = $data['subsonic-password'];

            $this->_config->kodi->enabled = $data['kodi-enabled'] == 'on' ? '1' : '0';
            $this->_config->kodi->URL = $data['kodi-url'];
            $this->_config->kodi->username = $data['kodi-username'];
            $this->_config->kodi->password = $data['kodi-password'];
            $this->_config->kodi->rotateMoviesInterval = $data['rotate-movies-interval'];
            $this->_config->kodi->rotateEpisodesInterval = $data['rotate-episodes-interval'];
            $this->_config->kodi->rotateAlbumsInterval = $data['rotate-albums-interval'];

            $this->_config->sickrage->enabled = $data['sickrage-enabled'] == 'on' ? '1' : '0';
            $this->_config->sickrage->URL = $data['sickrage-url'];
            $this->_config->sickrage->APIKey = $data['sickrage-apikey'];

            $this->_config->couchpotato->enabled = $data['couchpotato-enabled'] == 'on' ? '1' : '0';
            $this->_config->couchpotato->URL = $data['couchpotato-url'];
            $this->_config->couchpotato->APIKey = $data['couchpotato-apikey'];
            $this->_config->couchpotato->rotateInterval = $data['couchpotato-rotate-interval'];
        }

        return $valid;
    }
}
