<?php

namespace Chell\Forms;

/**
 * The form responsible for the dashboard settings.
 *
 * @package Forms
 */
class SettingsDashboardForm extends SettingsBaseForm
{
	/**
     * Add all fields to the form and set form specific attributes.
     */
	public function initialize()
	{
		$this->setFormFieldClasses('Dashboard');
	}
}
