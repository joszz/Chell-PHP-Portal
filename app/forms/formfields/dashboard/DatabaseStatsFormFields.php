<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Validation\Validator\Numericality;

class DatabaseStatsFormFields extends FormFields
{
	protected function initializeFields()
	{
		$this->fields[] = $dbStatsEnabled = new Check('databasestats-enabled');
		$dbStatsEnabled->setLabel('Enabled');
		$dbStatsEnabled->setAttributes([
			'value' => '1',
			'checked' => $this->form->settings->databasestats->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Database'
		]);

		$this->fields[] = $dbStatsHidden = new Hidden('databasestats-hidden');
		$dbStatsHidden->setLabel('');
		$dbStatsHidden->setAttributes(['fieldset' => true]);

        $this->fields[] = $dbStatsInterval = new Numeric('databasestats-update_interval');
		$dbStatsInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($this->form->settings->pulseway->update_interval)
			->addValidators([
				new Numericality(['message' => $this->form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $this->form->translator->validation['required'], 'with' => 'databasestats-enabled'])
			]);
	}
}