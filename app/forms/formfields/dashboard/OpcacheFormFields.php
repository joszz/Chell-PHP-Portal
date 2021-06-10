<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;

class OpcacheFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $opcacheEnabled = new Check('opcache-enabled');
		$opcacheEnabled->setLabel('Enabled');
		$opcacheEnabled->setAttributes([
			'value' => '1',
			'checked' => $this->form->settings->opcache->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Opcache'
		]);

		$this->fields[] = $opcacheHidden = new Hidden('opcache-hidden');
		$opcacheHidden->setLabel('');
		$opcacheHidden->setAttributes(['fieldset' => 'end']);
	}
}