<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;

class DatabaseStatsFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$dbStatsEnabled = new Check('database-stats-enabled');
		$dbStatsEnabled->setLabel('Enabled');
		$dbStatsEnabled->setAttributes([
			'checked' => $form->settings->database_stats->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'Database'
		]);

		$dbStatsHidden = new Hidden('database-stats-hidden');
		$dbStatsHidden->setLabel('');
		$dbStatsHidden->setAttributes(['fieldset' => 'end']);

		$form->add($dbStatsEnabled);
		$form->add($dbStatsHidden);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->database_stats->enabled = isset($data['database-stats-enabled']) && $data['database-stats-enabled'] == 'on' ? '1' : '0';
    }
}