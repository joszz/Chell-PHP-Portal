<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Filter\Validation\Validator\Numericality;

class SnmpFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = new Check('snmp-enabled', [
			'fieldset' => 'SNMP',
			'checked' => $this->form->settings->snmp->enabled == '1' ? 'checked' : null
		]);

		$this->fields[] = $snmpInterval = new Numeric('snmp-update_interval');
		$snmpInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->snmp->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'snmp-enabled'])
			]);
    }
}