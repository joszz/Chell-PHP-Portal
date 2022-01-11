<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;

class OpcacheFormFields extends FormFields
{
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