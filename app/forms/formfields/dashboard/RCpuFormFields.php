<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Url as UrlValidator;

class RCpuFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$rCpuEnabled = new Check('rcpu-enabled');
		$rCpuEnabled->setLabel('Enabled');
		$rCpuEnabled->setAttributes([
			'checked' => $form->settings->rcpu->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->settings->rcpu->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'rcpu-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$form->add($rCpuEnabled);
		$form->add($rCpuURL);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
	public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->rcpu->enabled = isset($data['rcpu-enabled']) && $data['rcpu-enabled'] == 'on' ? '1' : '0';
        $settings->rcpu->url = $data['rcpu-url'];
    }
}