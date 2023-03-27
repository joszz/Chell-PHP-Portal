<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;

/**
 * The formfields for the Devices plugin
 *
 * @package Formfields
 */
class DevicesFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('devices-enabled', [
			'fieldset' => 'Devices',
			'checked' => $this->form->settings->devices->enabled->value == '1' ? 'checked' : null
		]);
	}
}