<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;

/**
 * The formfields for the CPU plugin
 *
 * @package Formfields
 */
class CpuFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('cpu-enabled', [
			'fieldset' => 'CPU',
			'checked' => $this->form->settings->cpu->enabled == '1' ? 'checked' : null
		]);
	}
}