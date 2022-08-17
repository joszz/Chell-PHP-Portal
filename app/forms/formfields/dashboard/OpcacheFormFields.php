<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;

/**
 * The formfields for the Opcache plugin
 *
 * @package Formfields
 */
class OpcacheFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('opcache-enabled', [
			'fieldset' => 'Opcache',
			'checked' => $this->form->settings->opcache->enabled == '1' ? 'checked' : null
		]);
	}
}