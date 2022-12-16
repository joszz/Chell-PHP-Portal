<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the Verisure plugin
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
			'checked' => $this->form->settings->sysinfo->enabled == '1' ? 'checked' : null
		]);

        $this->fields[] = $pulsewayInterval = new Numeric('sysinfo-update_interval');
		$pulsewayInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->sysinfo->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'sysinfo-enabled'])
			]);
	}
}