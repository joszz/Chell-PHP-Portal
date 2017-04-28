<?php

namespace Chell\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * The form responsible for the general settings.
 *
 * @package Forms
 */
class SettingsGeneralForm extends Form
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
        $this->_action = 'general';

        $title = new Text('title');
        $title->setLabel('Title');
        $title->setFilters(array('striptags', 'string'));
        $title->setAttributes(array('class' => 'form-control'));
        $title->setDefault($this->_config->application->title);
        $title->addValidators(array(new PresenceOf(array())));

        $cryptKey = new Text('cryptkey');
        $cryptKey->setLabel('Cryptkey');
        $cryptKey->setFilters(array('striptags', 'string'));
        $cryptKey->setAttributes(array('class' => 'form-control'));
        $cryptKey->setDefault($this->_config->application->phalconCryptKey);
        $cryptKey->addValidators(array(new PresenceOf(array())));

        $bgcolor = new Select(
            'bgcolor',
            array('blackbg' => 'Black', 'whitebg' => 'White'),
            array('useEmpty' => false)
        );
        $bgcolor->setLabel('Background color');
        $bgcolor->setDefault($this->_config->application->background);

        $debug = new Check('debug');
        $debug->setLabel('Debug');
        $debug->setAttributes(array(
            'checked' => $this->_config->application->debug == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small'
        ));

        $duoEnabled = new Check('duo-enabled');
        $duoEnabled->setLabel('Duo enabled');
        $duoEnabled->setAttributes(array(
            'checked' => $this->_config->duo->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small'
        ));

        $duoAPIHostname = new Text('duo-apiHostname');
        $duoAPIHostname->setLabel('Duo API hostname');
        $duoAPIHostname->setFilters(array('striptags', 'string'));
        $duoAPIHostname->setAttributes(array('class' => 'form-control'));
        $duoAPIHostname->setDefault($this->_config->duo->apiHostname);

        $duoIKey = new Text('duo-ikey');
        $duoIKey->setLabel('Duo integration key');
        $duoIKey->setFilters(array('striptags', 'string'));
        $duoIKey->setAttributes(array('class' => 'form-control'));
        $duoIKey->setDefault($this->_config->duo->ikey);

        $duoSKey = new Text('duo-skey');
        $duoSKey->setLabel('Duo secret key');
        $duoSKey->setFilters(array('striptags', 'string'));
        $duoSKey->setAttributes(array('class' => 'form-control'));
        $duoSKey->setDefault($this->_config->duo->skey);

        $duoAKey = new Text('duo-akey');
        $duoAKey->setLabel('Duo akey');
        $duoAKey->setFilters(array('striptags', 'string'));
        $duoAKey->setAttributes(array('class' => 'form-control'));
        $duoAKey->setDefault($this->_config->duo->akey);

        $this->add($title);
        $this->add($bgcolor);
        $this->add($cryptKey);
        $this->add($debug);
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
            $this->_config->application->phalconCryptKey = $data['cryptkey'];
            $this->_config->application->background = $data['bgcolor'];
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
