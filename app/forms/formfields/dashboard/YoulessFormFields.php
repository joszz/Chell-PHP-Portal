<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Url as UrlValidator;

class YoulessFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$youlessEnabled = new Check('youless-enabled');
		$youlessEnabled->setLabel('Enabled')
			->setAttributes([
				'checked' => $form->settings->youless->enabled == '1' ? 'checked' : null,
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
			->setDefault($form->settings->youless->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'youless-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

        $youlessChartsURL = new Text('youless-charts-url');
		$youlessChartsURL->setLabel('Chart URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->youless->charts_url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'youless-enabled']),
				new UrlValidator(['message' => $form->translator->validation['url']])
			]);

		$youlessPassword = new Password('youless-password');
		$youlessPassword->setLabel('Password')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'autocomplete' => 'new-password', 'fieldset' => true])
			->setDefault($form->settings->youless->password)
			->addValidator(new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'youless-enabled']));

		$youlessInterval = new Numeric('youless-update-interval');
		$youlessInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->youless->update_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);

		$youlessPrimaryThreshold = new Numeric('youless-primary-threshold');
		$youlessPrimaryThreshold->setLabel('Primary threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->youless->threshold_primary)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);

		$youlessWarnThreshold = new Numeric('youless-warn-threshold');
		$youlessWarnThreshold->setLabel('Warn threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($form->settings->youless->threshold_warning)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);

		$youlessDangerThreshold = new Numeric('youless-danger-threshold');
		$youlessDangerThreshold->setLabel('Danger threshold')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->settings->youless->threshold_danger)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'youless-enabled'])
			]);

		$form->add($youlessEnabled);
		$form->add($youlessURL);
		$form->add($youlessChartsURL);
		$form->add($youlessPassword);
		$form->add($youlessInterval);
		$form->add($youlessPrimaryThreshold);
		$form->add($youlessWarnThreshold);
		$form->add($youlessDangerThreshold);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->youless->enabled = isset($data['youless-enabled']) && $data['youless-enabled'] == 'on' ? '1' : '0';
        $settings->youless->url = $data['youless-url'];
		$settings->youless->charts_url = $data['youless-charts-url'];
        $settings->youless->password = $data['youless-password'];
        $settings->youless->update_interval = $data['youless-update-interval'];
        $settings->youless->threshold_primary = $data['youless-primary-threshold'];
        $settings->youless->threshold_warning = $data['youless-warn-threshold'];
        $settings->youless->threshold_danger = $data['youless-danger-threshold'];
    }
}