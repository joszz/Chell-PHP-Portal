<?php

namespace Chell\Forms;

use Phalcon\Forms\Element\Numeric;
use Phalcon\Validation\Validator\Numericality;

/**
 * The form responsible for the dashboard settings.
 *
 * @package Forms
 */
class SettingsDashboardForm extends SettingsBaseForm
{
	private $formFieldClasses = [];

	/**
     * Add all fields to the form and set form specific attributes.
     */
	public function initialize()
	{
		$this->setAction($this->_config->application->baseUri . 'settings/dashboard#dashboard');

		$devicestateTimeouts = new Numeric('check-devicestate-interval');
		$devicestateTimeouts->setLabel('Check device state interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->setDefault($this->_config->dashboard->checkDeviceStatesInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));
		$this->add($devicestateTimeouts);

		$formFieldFiles = array_diff(scandir(APP_PATH . $this->config->application->formsDir . 'dashboard/'), array('..', '.', 'IDashboardFormFields.php'));
		foreach($formFieldFiles as $file)
        {
			$class =  'Chell\Forms\Dashboard\\' . basename($file, '.php');
			$this->formFieldClasses[] = new $class;
        }

		foreach($this->formFieldClasses as $class)
        {
			$class->setFields($this);
        }
	}

	/**
     * Check if form is valid. If so set the values to the config array.
     *
     * @param   array     $data     The form data posted.
     * @param   object    $entity   The entity to validate.
     * @return  bool                Whether or not form is valid.
     */
	public function IsValid($data = null, $entity = null) : bool
	{
		$valid = parent::IsValid($data, $entity);

		if ($valid)
		{
			$this->_config->dashboard->checkDeviceStatesInterval = $data['check-devicestate-interval'];

            foreach($this->formFieldClasses as $class)
            {
                $class->setPostData($this->_config, $data);
            }
		}

		return $valid;
	}
}
