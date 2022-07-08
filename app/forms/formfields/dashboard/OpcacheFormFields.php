<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;

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
		$this->fields[] = new Check('opcache-enabled', [
			'fieldset' => 'Opcache',
			'checked' => $this->form->settings->opcache->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $opcacheHidden = new Hidden('opcache-hidden');
		$opcacheHidden->setLabel('');
		$opcacheHidden->setAttributes(['fieldset' => 'end']);
	}
}