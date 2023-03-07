<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Filter\Validation\Validator\Numericality;

/**
 * The formfields for the Database stats plugin
 *
 * @package Formfields
 */
class DatabaseStatsFormFields extends FormFields
{
    /**
     * Add all fields to the form.
     */
	protected function initializeFields()
	{
		$this->hasFieldset = true;

		$this->fields[] = new Check('databasestats-enabled', [
			'fieldset' => 'Database',
			'checked' => $this->form->settings->databasestats->enabled == '1' ? 'checked' : null
		]);

        $this->fields[] = $dbStatsInterval = new Numeric('databasestats-update_interval');
		$dbStatsInterval->setLabel('Update interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->form->settings->databasestats->update_interval ?? 30)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'databasestats-enabled'])
			]);
	}
}