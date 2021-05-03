<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;

class RCpuFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$rCpuEnabled = new Check('rcpu-enabled');
		$rCpuEnabled->setLabel('Enabled');
		$rCpuEnabled->setAttributes([
			'checked' => $form->config->rcpu->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'rCPU',
		]);

		$rCpuURL = new Text('rcpu-url');
		$rCpuURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->config->rcpu->URL)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'rcpu-enabled']));

		$form->add($rCpuEnabled);
		$form->add($rCpuURL);
	}

    /**
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
	public function setPostData(&$config, $data)
    {
        $config->rcpu->enabled = isset($data['rcpu-enabled']) && $data['rcpu-enabled'] == 'on' ? '1' : '0';
        $config->rcpu->URL = $data['rcpu-url'];
    }
}