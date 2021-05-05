<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class MotionFormFields implements IFormFields
	{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$motionEnabled = new Check('motion-enabled');
		$motionEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->config->motion->enabled == '1' ? 'checked' : null,
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small',
				'fieldset' => 'Motion'
		]);

		$motionURL = new Text('motion-url');
		$motionURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->motion->URL)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'motion-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$motionPicturePath = new Text('motion-picturepath');
		$motionPicturePath->setLabel('Picture path')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->config->motion->picturePath)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'motion-enabled']));

		$motionInterval = new Numeric('motion-update-interval');
		$motionInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->motion->updateInterval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'motion-enabled'])
			]);

		$form->add($motionEnabled);
		$form->add($motionURL);
		$form->add($motionPicturePath);
		$form->add($motionInterval);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$config, $data)
    {
        $config->motion->enabled = isset($data['motion-enabled']) && $data['motion-enabled'] == 'on' ? '1' : '0';
        $config->motion->URL = $data['motion-url'];
        $config->motion->picturePath = $data['motion-picturepath'];
        $config->motion->updateInterval = $data['motion-update-interval'];
    }
}