<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the SNMP plugin
 *
 * @package Formfields
 */
class SnmpFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('snmp-enabled', [
			'fieldset' => 'SNMP',
			'checked' => $this->form->settings->snmp->enabled->value == '1' ? 'checked' : null
		]);

		$this->fields[] = $snmpInterval = new Numeric('snmp-update_interval');
		$snmpInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->snmp->update_interval->value ?? 30)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'snmp-enabled'])
			]);
    }
}