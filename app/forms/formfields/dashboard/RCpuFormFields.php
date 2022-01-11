<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

class RCpuFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = new Check('rcpu-enabled', [
			'fieldset' => 'rCPU',
			'checked' => $this->form->settings->rcpu->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $rCpuURL = new Text('rcpu-url');
		$rCpuURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->rcpu->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'rcpu-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);
	}
}