<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class RoborockFormFields {
	/**
     * Adds fields to the form.
     */
	public static function setFields($form)
	{
		$roborockEnabled = new Check('roborock-enabled');
		$roborockEnabled->setLabel('Enabled');
		$roborockEnabled->setAttributes([
			'checked' => $form->_config->roborock->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Roborock'
		]);

        $roborockInterval = new Numeric('roborock-update-interval');
		$roborockInterval->setLabel('Roborock interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->roborock->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

        $roborockIp = new Text('roborock-ip');
		$roborockIp->setLabel('IP')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($form->_config->roborock->ip);

		$roborockToken = new Text('roborock-token');
		$roborockToken->setLabel('Token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->roborock->token);

		$form->add($roborockEnabled);
		$form->add($roborockInterval);
		$form->add($roborockIp);
		$form->add($roborockToken);
	}

    public static function setPostData(&$config, $data)
    {
        $config->roborock->enabled = isset($data['roborock-enabled']) && $data['roborock-enabled'] == 'on' ? '1' : '0';
        $config->roborock->updateInterval = $data['roborock-update-interval'];
        $config->roborock->ip = $data['roborock-ip'];
        $config->roborock->token = $data['roborock-token'];
    }
}