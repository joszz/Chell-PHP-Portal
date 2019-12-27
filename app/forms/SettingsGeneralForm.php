<?php

namespace Chell\Forms;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;

/**
 * The form responsible for the general settings.
 *
 * @package Forms
 */
class SettingsGeneralForm extends SettingsBaseForm
{
    /**
     * Add all fields to the form and set form specific attributes.
     */
    public function initialize()
    {
        $this->setAction($this->_config->application->baseUri . 'settings/general');

        $title = new Text('title');
        $title->setLabel('Title');
        $title->setFilters(array('striptags', 'string'));
        $title->setAttributes(array('class' => 'form-control'));
        $title->setDefault($this->_config->application->title);
        $title->addValidators(array(new PresenceOf(array())));

        $bgcolor = new Select(
            'bgcolor',
            array('blackbg' => 'Black', 'whitebg' => 'White', 'time' => 'Time based'),
            array('useEmpty' => false)
        );
        $bgcolor->setLabel('Background color');
        $bgcolor->setDefault($this->_config->application->background);

        $alertTimeout = new Numeric('alert-timeout');
        $alertTimeout->setLabel('Alert timeout')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->application->alertTimeout)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $itemsPerPage = new Numeric('items-per-page');
        $itemsPerPage->setLabel('Items per page')
            ->setFilters(array('striptags', 'int'))
            ->setAttributes(array('class' => 'form-control'))
            ->setDefault($this->_config->application->itemsPerPage)
            ->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'message' => 'Not a number')));

        $cryptKey = new Text('cryptkey');
        $cryptKey->setLabel('Cryptkey');
        $cryptKey->setFilters(array('striptags', 'string'));
        $cryptKey->setAttributes(array('class' => 'form-control'));
        $cryptKey->setDefault($this->_config->application->phalconCryptKey);
        $cryptKey->addValidators(array(new PresenceOf(array())));

        $tmdbAPIKey = new Text('tmdb-apikey');
        $tmdbAPIKey->setLabel('TMDB API key');
        $tmdbAPIKey->setFilters(array('striptags', 'string'));
        $tmdbAPIKey->setAttributes(array('class' => 'form-control'));
        $tmdbAPIKey->setDefault($this->_config->application->tmdbAPIKey);

        $whatIsMyBrowserAPIKey = new Text('whatismybrowser-apikey');
        $whatIsMyBrowserAPIKey->setLabel('WhatIsMyBrowser API key');
        $whatIsMyBrowserAPIKey->setFilters(array('striptags', 'string'));
        $whatIsMyBrowserAPIKey->setAttributes(array('class' => 'form-control'));
        $whatIsMyBrowserAPIKey->setDefault($this->_config->application->whatIsMyBrowserAPIKey);

        $whatIsMyBrowserAPIURL = new Text('whatismybrowser-apiurl');
        $whatIsMyBrowserAPIURL->setLabel('WhatIsMyBrowser API URL');
        $whatIsMyBrowserAPIURL->setFilters(array('striptags', 'string'));
        $whatIsMyBrowserAPIURL->setAttributes(array('class' => 'form-control'));
        $whatIsMyBrowserAPIURL->setDefault($this->_config->application->whatIsMyBrowserAPIURL);

        $debug = new Check('debug');
        $debug->setLabel('Debug');
        $debug->setAttributes(array(
            'checked' => $this->_config->application->debug == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small'
        ));

        $this->add($title);
        $this->add($bgcolor);
        $this->add($alertTimeout);
        $this->add($itemsPerPage);
        $this->add($cryptKey);
        $this->add($tmdbAPIKey);
        $this->add($whatIsMyBrowserAPIKey);
        $this->add($whatIsMyBrowserAPIURL);
        $this->add($debug);
        $this->setDuoFields();
    }

    /**
     * Adds Duo fields to the form.
     */
    private function setDuoFields()
    {
        $duoEnabled = new Check('duo-enabled');
        $duoEnabled->setLabel('Enabled');
        $duoEnabled->setAttributes(array(
            'checked' => $this->_config->duo->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Duo'
        ));

        $duoAPIHostname = new Text('duo-apiHostname');
        $duoAPIHostname->setLabel('API hostname');
        $duoAPIHostname->setFilters(array('striptags', 'string'));
        $duoAPIHostname->setAttributes(array('class' => 'form-control', 'fieldset' => true));
        $duoAPIHostname->setDefault($this->_config->duo->apiHostname);

        $duoIKey = new Text('duo-ikey');
        $duoIKey->setLabel('Integration key');
        $duoIKey->setFilters(array('striptags', 'string'));
        $duoIKey->setAttributes(array('class' => 'form-control', 'fieldset' => true));
        $duoIKey->setDefault($this->_config->duo->ikey);

        $duoSKey = new Text('duo-skey');
        $duoSKey->setLabel('Secret key');
        $duoSKey->setFilters(array('striptags', 'string'));
        $duoSKey->setAttributes(array('class' => 'form-control', 'fieldset' => true));
        $duoSKey->setDefault($this->_config->duo->skey);

        $duoAKey = new Text('duo-akey');
        $duoAKey->setLabel('Akey');
        $duoAKey->setFilters(array('striptags', 'string'));
        $duoAKey->setAttributes(array('class' => 'form-control', 'fieldset' => 'end'));
        $duoAKey->setDefault($this->_config->duo->akey);

        $this->add($duoEnabled);
        $this->add($duoAPIHostname);
        $this->add($duoIKey);
        $this->add($duoSKey);
        $this->add($duoAKey);
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
            $this->_config->application->title = $data['title'];
            $this->_config->application->background = $data['bgcolor'];
            $this->_config->application->alertTimeout = $data['alert-timeout'];
            $this->_config->application->itemsPerPage = $data['items-per-page'];
            $this->_config->application->phalconCryptKey = $data['cryptkey'];
            $this->_config->application->tmdbAPIKey = $data['tmdb-apikey'];
            $this->_config->application->whatIsMyBrowserAPIKey = $data['whatismybrowser-apikey'];
            $this->_config->application->whatIsMyBrowserAPIURL = $data['whatismybrowser-apiurl'];
            $this->_config->application->debug = $data['debug'] == 'on' ? '1' : '0';


            $this->_config->duo->enabled = $data['duo-enabled'] == 'on' ? '1' : '0';
            $this->_config->duo->apiHostname = $data['duo-apiHostname'];
            $this->_config->duo->ikey = $data['duo-ikey'];
            $this->_config->duo->skey = $data['duo-skey'];
            $this->_config->duo->akey = $data['duo-akey'];
        }

        return $valid;
    }
}
