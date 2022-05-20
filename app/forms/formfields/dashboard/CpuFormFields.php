<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

class CpuFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = new Check('cpu-enabled', [
			'fieldset' => 'CPU',
			'checked' => $this->form->settings->cpu->enabled == '1' ? 'checked' : null
		]);
	}
}