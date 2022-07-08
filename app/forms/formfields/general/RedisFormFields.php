<?php

namespace Chell\Forms\FormFields\General;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\Hibp;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the Apache plugin
 *
 * @package Formfields
 */
class RedisFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
        $this->fields[] = new Check('redis-enabled', [
            'fieldset' => 'Redis',
            'checked' => $this->form->settings->redis->enabled == '1' ? 'checked' : null
        ]);

        $this->fields[] = $redisHost = new Text('redis-host');
        $redisHost->setLabel('Host')
                  ->setFilters(['striptags', 'string'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($this->form->settings->redis->host)
                  ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'redis-enabled']));

        $this->fields[] =  $redisPort = new Numeric('redis-port');
        $redisPort->setLabel('Port')
                  ->setFilters(['striptags', 'int'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($this->form->settings->redis->port)
                  ->addValidator(new Numericality(['message' => $this->form->translator->validation['not-a-number']]))
                  ->addValidator(new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'redis-enabled']));

        $this->fields[] = $redisAuth = new Password('redis-auth');
        $redisAuth->setLabel('Auth')
                  ->setFilters(['striptags', 'string'])
                  ->setAttributes(['class' => 'form-control', 'fieldset' => true])
                  ->setDefault($this->form->settings->redis->auth);

        if ($this->form->settings->hibp->enabled)
        {
			$redisAuth->addValidator(new Hibp(['message' => $this->form->translator->validation['hibp']]));
        }
	}
}