<?php

namespace Chell\Forms;

use Phalcon\Mvc\Model;

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
		$this->setAction($this->settings->application->base_uri . 'settings/dashboard#dashboard');
		$this->setFormFieldClasses('Dashboard');
	}

	/**
     * Check if form is valid. If so set the values to the config array.
     *
     * @param   array     $data     The form data posted.
     * @param   Model    $entity    The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
	public function isValid(array $data = null, Model $entity = null) : bool
	{
		$valid = parent::isValid($data, $entity);

		if ($valid)
		{
            foreach($this->formFieldClasses as $class)
            {
                $class->setPostData($this->settings, $data);
            }
		}

		return $valid;
	}
}
