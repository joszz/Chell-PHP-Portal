<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Ip;

class RoborockFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$roborockEnabled = new Check('roborock-enabled');
		$roborockEnabled->setLabel('Enabled');
		$roborockEnabled->setAttributes([
			'checked' => $form->config->roborock->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Roborock'
		]);

        $roborockInterval = new Numeric('roborock-update-interval');
		$roborockInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->roborock->updateInterval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'roborock-enabled'])
			]);

		$roborockIp = new Text('roborock-ip');
		$roborockIp->setLabel('IP')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->config->roborock->ip)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'roborock-enabled']),
				new Ip(['message' => $form->translator->validation['ip'], 'allowPrivate' => true]),
			]);

		$roborockToken = new Password('roborock-token');
		$roborockToken->setLabel('Token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->roborock->token)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'roborock-enabled']));

		$form->add($roborockEnabled);
		$form->add($roborockInterval);
		$form->add($roborockIp);
		$form->add($roborockToken);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->roborock->enabled = isset($data['roborock-enabled']) && $data['roborock-enabled'] == 'on' ? '1' : '0';
        $config->roborock->updateInterval = $data['roborock-update-interval'];
        $config->roborock->ip = $data['roborock-ip'];
        $config->roborock->token = $data['roborock-token'];
    }
}