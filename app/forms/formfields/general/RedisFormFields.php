<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class RedisFormFields extends FormFields
{
	protected function initializeFields()
	{
        $this->fields[] =  $redisEnabled = new Check('redis-enabled');
        $redisEnabled->setLabel('Enabled');
        $redisEnabled->setAttributes([
            'value' => '1',
            'checked' => $this->form->settings->redis->enabled == '1' ? 'checked' : null,
            'data-toggle' => 'toggle',
            'data-onstyle' => 'success',
            'data-offstyle' => 'danger',
            'data-size' => 'small',
            'fieldset' => 'Redis'
        ]);

        $this->fields[] = $redisHost = new Text('redis-host');
        $redisHost->setLabel('Host')
                  ->setFilters(['striptags', 'string'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($this->form->settings->redis->host)
                  ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'imageproxy-enabled']));

        $this->fields[] =  $redisPort = new Numeric('redis-port');
        $redisPort->setLabel('Port')
                  ->setFilters(['striptags', 'int'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($this->form->settings->redis->port)
                  ->addValidator(new Numericality(['message' => $this->form->translator->validation['not-a-number']]))
                  ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'imageproxy-enabled']));

        $this->fields[] = $redisAuth = new Password('redis-auth');
        $redisAuth->setLabel('Auth')
                  ->setFilters(['striptags', 'string'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($this->form->settings->redis->auth);
	}
}