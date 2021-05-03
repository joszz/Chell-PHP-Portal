<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;

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
	public function setFields($form)
	{
        $redisEnabled = new Check('redis-enabled');
        $redisEnabled->setLabel('Enabled');
        $redisEnabled->setAttributes([
            'checked' => $form->config->redis->enabled == '1' ? 'checked' : null,
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
                  ->setDefault($form->config->redis->host)
                  ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'imageproxy-enabled']));

        $redisPort = new Numeric('redis-port');
        $redisPort->setLabel('Port')
                  ->setFilters(['striptags', 'int'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($form->config->redis->port)
                  ->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
                  ->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'imageproxy-enabled']));

        $redisAuth = new Password('redis-auth');
        $redisAuth->setLabel('Auth')
                  ->setFilters(['striptags', 'string'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($form->config->redis->auth);

        $form->add($redisEnabled);
        $form->add($redisHost);
        $form->add($redisPort);
        $form->add($redisAuth);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->redis->enabled = isset($data['redis-enabled']) && $data['redis-enabled'] == 'on' ? '1' : '0';
        $config->redis->host = $data['redis-host'];
        $config->redis->port = $data['redis-port'];
        $config->redis->auth = $data['redis-auth'];
    }
}