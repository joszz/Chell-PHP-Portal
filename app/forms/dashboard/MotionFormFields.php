<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;

class MotionFormFields implements IDashboardFormFields
	{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$motionEnabled = new Check('motion-enabled');
		$motionEnabled->setLabel('Enabled');
		$motionEnabled->setAttributes([
			'checked' => $form->_config->motion->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->_config->motion->URL);

		$motionPicturePath = new Text('motion-picturepath');
		$motionPicturePath->setLabel('Picture path')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->_config->motion->picturePath);

		$motionInterval = new Numeric('motion-update-interval');
		$motionInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->motion->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));


		$form->add($motionEnabled);
		$form->add($motionURL);
		$form->add($motionPicturePath);
		$form->add($motionInterval);
	}

    public function setPostData(&$config, $data)
    {
        $config->motion->enabled = isset($data['motion-enabled']) && $data['motion-enabled'] == 'on' ? '1' : '0';
        $config->motion->URL = $data['motion-url'];
        $config->motion->picturePath = $data['motion-picturepath'];
        $config->motion->updateInterval = $data['motion-update-interval'];
    }
}