<?php

namespace Chell\Forms;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
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
        $title->setFilters(['striptags', 'string']);
        $title->setAttributes(['class' => 'form-control']);
        $title->setDefault($this->_config->application->title);
        $title->addValidators([new PresenceOf([])]);

        $bgcolor = new Select(
            'bgcolor',
            ['blackbg' => 'Black', 'whitebg' => 'White', 'time' => 'Time based'],
            ['useEmpty' => false]
        );
        $bgcolor->setLabel('Background color');
        $bgcolor->setDefault($this->_config->application->background);

        $alertTimeout = new Numeric('alert-timeout');
        $alertTimeout->setLabel('Alert timeout')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->_config->application->alertTimeout)
            ->addValidator(new Regex(['pattern' => '/^[0-9]+$/', 'message' => 'Not a number']));

        $itemsPerPage = new Numeric('items-per-page');
        $itemsPerPage->setLabel('Items per page')
            ->setFilters(['striptags', 'int'])
            ->setAttributes(['class' => 'form-control'])
            ->setDefault($this->_config->application->itemsPerPage)
            ->addValidator(new Regex(['pattern' => '/^[0-9]+$/', 'message' => 'Not a number']));

        $cryptKey = new Password('cryptkey');
        $cryptKey->setLabel('Cryptkey');
        $cryptKey->setFilters(['striptags', 'string']);
        $cryptKey->setAttributes(['class' => 'form-control']);
        $cryptKey->setDefault($this->_config->application->phalconCryptKey);
        $cryptKey->addValidators([new PresenceOf([])]);

        $tmdbAPIKey = new Password('tmdb-apikey');
        $tmdbAPIKey->setLabel('TMDB API key');
        $tmdbAPIKey->setFilters(['striptags', 'string']);
        $tmdbAPIKey->setAttributes(['class' => 'form-control']);
        $tmdbAPIKey->setDefault($this->_config->application->tmdbAPIKey);

        $whatIsMyBrowserAPIKey = new Password('whatismybrowser-apikey');
        $whatIsMyBrowserAPIKey->setLabel('WhatIsMyBrowser API key');
        $whatIsMyBrowserAPIKey->setFilters(['striptags', 'string']);
        $whatIsMyBrowserAPIKey->setAttributes(['class' => 'form-control']);
        $whatIsMyBrowserAPIKey->setDefault($this->_config->application->whatIsMyBrowserAPIKey);

        $whatIsMyBrowserAPIURL = new Text('whatismybrowser-apiurl');
        $whatIsMyBrowserAPIURL->setLabel('WhatIsMyBrowser API URL');
        $whatIsMyBrowserAPIURL->setFilters(['striptags', 'string']);
        $whatIsMyBrowserAPIURL->setAttributes(['class' => 'form-control']);
        $whatIsMyBrowserAPIURL->setDefault($this->_config->application->whatIsMyBrowserAPIURL);

        $debug = new Check('debug');
        $debug->setLabel('Debug');
        $debug->setAttributes([
            'checked' => $this->_config->application->debug == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small'
        ]);

        $demo = new Check('demo');
        $demo->setLabel('Demo mode');
        $demo->setAttributes([
            'checked' => $this->_config->application->demoMode == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small'
        ]);

        $this->add($title);
        $this->add($bgcolor);
        $this->add($alertTimeout);
        $this->add($itemsPerPage);
        $this->add($cryptKey);
        $this->add($tmdbAPIKey);
        $this->add($whatIsMyBrowserAPIKey);
        $this->add($whatIsMyBrowserAPIURL);
        $this->add($debug);
        $this->add($demo);
        $this->setDuoFields();
        $this->setImageProxyFields();
        $this->setRedisFields();
    }

    /**
     * Adds Duo fields to the form.
     */
    private function setDuoFields()
    {
        $duoEnabled = new Check('duo-enabled');
        $duoEnabled->setLabel('Enabled');
        $duoEnabled->setAttributes([
            'checked' => $this->_config->duo->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Duo'
        ]);

        $duoAPIHostname = new Text('duo-apiHostname');
        $duoAPIHostname->setLabel('API hostname');
        $duoAPIHostname->setFilters(['striptags', 'string']);
        $duoAPIHostname->setAttributes(['class' => 'form-control', 'fieldset' => true]);
        $duoAPIHostname->setDefault($this->_config->duo->apiHostname);

        $duoIKey = new Password('duo-ikey');
        $duoIKey->setLabel('Integration key');
        $duoIKey->setFilters(['striptags', 'string']);
        $duoIKey->setAttributes(['class' => 'form-control', 'fieldset' => true]);
        $duoIKey->setDefault($this->_config->duo->ikey);

        $duoSKey = new Password('duo-skey');
        $duoSKey->setLabel('Secret key');
        $duoSKey->setFilters(['striptags', 'string']);
        $duoSKey->setAttributes(['class' => 'form-control', 'fieldset' => true]);
        $duoSKey->setDefault($this->_config->duo->skey);

        $duoAKey = new Password('duo-akey');
        $duoAKey->setLabel('Akey');
        $duoAKey->setFilters(['striptags', 'string']);
        $duoAKey->setAttributes(['class' => 'form-control', 'fieldset' => 'end']);
        $duoAKey->setDefault($this->_config->duo->akey);

        $this->add($duoEnabled);
        $this->add($duoAPIHostname);
        $this->add($duoIKey);
        $this->add($duoSKey);
        $this->add($duoAKey);
    }

    /**
     * Adds ImageProxy fields to the form.
     */
    private function setImageProxyFields(){
        $imageproxyEnabled = new Check('imageproxy-enabled');
        $imageproxyEnabled->setLabel('Enabled');
        $imageproxyEnabled->setAttributes([
            'checked' => $this->_config->imageproxy->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Imageproxy'
        ]);

        $imageproxyUrl = new Text('imageproxy-url');
        $imageproxyUrl->setLabel('URL');
        $imageproxyUrl->setFilters(['striptags', 'string']);
        $imageproxyUrl->setAttributes(['class' => 'form-control', 'fieldset' => true]);
        $imageproxyUrl->setDefault($this->_config->imageproxy->URL);

        $this->add($imageproxyEnabled);
        $this->add($imageproxyUrl);
    }

    /**
     * Adds Redis fields to the form.
     */
    private function setRedisFields(){
        $redisEnabled = new Check('redis-enabled');
        $redisEnabled->setLabel('Enabled');
        $redisEnabled->setAttributes([
            'checked' => $this->_config->redis->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Redis'
        ]);

        $redisHost = new Text('redis-host');
        $redisHost->setLabel('Host');
        $redisHost->setFilters(['striptags', 'string']);
        $redisHost->setAttributes(['class' => 'form-control', 'fieldset' => true]);
        $redisHost->setDefault($this->_config->redis->host);

        $redisPort = new Text('redis-port');
        $redisPort->setLabel('Port');
        $redisPort->setFilters(['striptags', 'int']);
        $redisPort->setAttributes(['class' => 'form-control', 'fieldset' => true]);
        $redisPort->setDefault($this->_config->redis->port);

        $redisAuth = new Password('redis-auth');
        $redisAuth->setLabel('Auth');
        $redisAuth->setFilters(['striptags', 'string']);
        $redisAuth->setAttributes(['class' => 'form-control', 'fieldset' => true]);
        $redisAuth->setDefault($this->_config->redis->auth);

        $this->add($redisEnabled);
        $this->add($redisHost);
        $this->add($redisPort);
        $this->add($redisAuth);
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
            $this->_config->application->demoMode = $data['demo'] == 'on' ? '1' : '0';

            $this->_config->duo->enabled = $data['duo-enabled'] == 'on' ? '1' : '0';
            $this->_config->duo->apiHostname = $data['duo-apiHostname'];
            $this->_config->duo->ikey = $data['duo-ikey'];
            $this->_config->duo->skey = $data['duo-skey'];
            $this->_config->duo->akey = $data['duo-akey'];

            $this->_config->imageproxy->enabled = $data['imageproxy-enabled'] == 'on' ? '1' : '0';
            $this->_config->imageproxy->URL = $data['imageproxy-url'];

            $this->_config->redis->enabled = $data['redis-enabled'] == 'on' ? '1' : '0';
            $this->_config->redis->host = $data['redis-host'];
            $this->_config->redis->port = $data['redis-port'];
            $this->_config->redis->auth = $data['redis-auth'];
        }

        return $valid;
    }
}
