<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Regex;

class YoulessFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$youlessEnabled = new Check('youless-enabled');
		$youlessEnabled->setLabel('Enabled');
		$youlessEnabled->setAttributes([
			'checked' => $form->_config->youless->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Youless'
		]);

		$youlessURL = new Text('youless-url');
		$youlessURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->youless->URL);

		$youlessPassword = new Password('youless-password');
		$youlessPassword->setLabel('YouLess password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($form->_config->youless->password);

		$youlessInterval = new Numeric('youless-update-interval');
		$youlessInterval->setLabel('YouLess interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->youless->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$youlessPrimaryThreshold = new Numeric('youless-primary-threshold');
		$youlessPrimaryThreshold->setLabel('YouLess primary threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->youless->primaryThreshold)
			->addValidator(new Regex(['pattern' => '/^[0-9]+$/', 'message' => 'Not a number']));

		$youlessWarnThreshold = new Numeric('youless-warn-threshold');
		$youlessWarnThreshold->setLabel('YouLess warn threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->youless->warnThreshold)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$youlessDangerThreshold = new Numeric('youless-danger-threshold');
		$youlessDangerThreshold->setLabel('YouLess danger threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->youless->dangerThreshold)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$form->add($youlessEnabled);
		$form->add($youlessURL);
		$form->add($youlessPassword);
		$form->add($youlessInterval);
		$form->add($youlessPrimaryThreshold);
		$form->add($youlessWarnThreshold);
		$form->add($youlessDangerThreshold);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->youless->enabled = isset($data['youless-enabled']) && $data['youless-enabled'] == 'on' ? '1' : '0';
        $config->youless->URL = $data['youless-url'];
        $config->youless->password = $data['youless-password'];
        $config->youless->updateInterval = $data['youless-update-interval'];
        $config->youless->primaryThreshold = $data['youless-primary-threshold'];
        $config->youless->warnThreshold = $data['youless-warn-threshold'];
        $config->youless->dangerThreshold = $data['youless-danger-threshold'];
    }
}