<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class YoulessFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$youlessEnabled = new Check('youless-enabled');
		$youlessEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->youless->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->config->youless->URL)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$youlessPassword = new Password('youless-password');
		$youlessPassword->setLabel('YouLess password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($form->config->youless->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$youlessInterval = new Numeric('youless-update-interval');
		$youlessInterval->setLabel('YouLess interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->youless->updateInterval)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$youlessPrimaryThreshold = new Numeric('youless-primary-threshold');
		$youlessPrimaryThreshold->setLabel('YouLess primary threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->youless->primaryThreshold)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$youlessWarnThreshold = new Numeric('youless-warn-threshold');
		$youlessWarnThreshold->setLabel('YouLess warn threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->youless->warnThreshold)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

		$youlessDangerThreshold = new Numeric('youless-danger-threshold');
		$youlessDangerThreshold->setLabel('YouLess danger threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->youless->dangerThreshold)
			->addValidator(new Numericality(['message' => $form->translator->validation['not-a-number']]))
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'verisure-enabled']));

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