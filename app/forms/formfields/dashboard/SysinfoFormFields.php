<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the Sysinfo plugin
 *
 * @package Formfields
 */
class SysinfoFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('sysinfo-enabled', [
			'fieldset' => 'Sysinfo',
			'checked' => $this->form->settings->sysinfo->enabled->value == '1' ? 'checked' : null
		]);

        $this->fields[] = $interval = new Numeric('sysinfo-update_interval');
		$interval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sysinfo->update_interval->value ?? 30)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sysinfo-enabled'])
			]);
	}
}