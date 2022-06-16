<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Filter\Validation\Validator\Numericality;

class DockerFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = new Check('docker-enabled', [
			'fieldset' => 'Docker',
			'checked' => $this->form->settings->docker->enabled == '1' ? 'checked' : null
		]);

        $this->fields[] = $pulsewayInterval = new Numeric('docker-update_interval');
		$pulsewayInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => true])
			->setDefault($this->form->settings->docker->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'docker-enabled'])
			]);
	}
}