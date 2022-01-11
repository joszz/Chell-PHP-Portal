<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Filter\Validation\Validator\Url as UrlValidator;

class PiholeFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = new Check('pihole-enabled', [
			'fieldset' => 'Pi-hole',
			'checked' => $this->form->settings->pihole->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $piholeURL = new Text('pihole-url');
		$piholeURL->setLabel('URL')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->pihole->url)
			->addValidators([
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'pihole-enabled']),
				new UrlValidator(['message' => $this->form->translator->validation['url'], 'allowEmpty' => true])
			]);
	}
}