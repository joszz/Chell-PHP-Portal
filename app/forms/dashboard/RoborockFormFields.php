<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class RoborockFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
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

		$roborockToken = new Password('roborock-token');
		$roborockToken->setLabel('Token')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->roborock->token);

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