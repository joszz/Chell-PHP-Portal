<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;

class OpcacheFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$opcacheEnabled = new Check('opcache-enabled');
		$opcacheEnabled->setLabel('Enabled');
		$opcacheEnabled->setAttributes([
			'checked' => $form->settings->opcache->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Opcache'
		]);

		$opcacheHidden = new Hidden('opcache-hidden');
		$opcacheHidden->setLabel('');
		$opcacheHidden->setAttributes(['fieldset' => 'end']);

		$form->add($opcacheEnabled);
		$form->add($opcacheHidden);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->opcache->enabled = isset($data['opcache-enabled']) && $data['opcache-enabled'] == 'on' ? '1' : '0';
    }
}