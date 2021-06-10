<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\FormFields;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;

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
		$dbStatsHidden->setAttributes(['fieldset' => 'end']);
	}
}