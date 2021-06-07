<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class RedisFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
        $redisEnabled = new Check('redis-enabled');
        $redisEnabled->setLabel('Enabled');
        $redisEnabled->setAttributes([
            'checked' => $form->settings->redis->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Redis'
        ]);

        $redisHost = new Text('redis-host');
        $redisHost->setLabel('Host')
                  ->setFilters(['striptags', 'string'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($form->settings->redis->host)
                  ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'imageproxy-enabled']));

        $redisPort = new Numeric('redis-port');
        $redisPort->setLabel('Port')
                  ->setFilters(['striptags', 'int'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($form->settings->redis->port)
                  ->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
                  ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'imageproxy-enabled']));

        $redisAuth = new Password('redis-auth');
        $redisAuth->setLabel('Auth')
                  ->setFilters(['striptags', 'string'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($form->settings->redis->auth);

        $form->add($redisEnabled);
        $form->add($redisHost);
        $form->add($redisPort);
        $form->add($redisAuth);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->redis->enabled = isset($data['redis-enabled']) && $data['redis-enabled'] == 'on' ? '1' : '0';
        $settings->redis->host = $data['redis-host'];
        $settings->redis->port = $data['redis-port'];
        $settings->redis->auth = $data['redis-auth'];
    }
}