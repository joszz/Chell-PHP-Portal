<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\SettingsBaseForm;
use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Chell\Models\SettingsContainer;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Validation\Validator\Numericality;

class SnmpFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields(SettingsBaseForm $form)
	{
		$snmpEnabled = new Check('snmp-enabled');
		$snmpEnabled->setLabel('Enabled');
		$snmpEnabled->setAttributes([
			'checked' => $form->settings->snmp->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'SNMP'
		]);

		$snmpInterval = new Numeric('snmp-update-interval');
		$snmpInterval->setLabel('Interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->settings->snmp->update_interval)
			->addValidators([
				new Numericality(['message' => $form->translator->validation['not-a-number']]),
				new PresenceOfConfirmation(['message' => $form->translator->validation['required'], 'with' => 'snmp-enabled'])
			]);

		$form->add($snmpEnabled);
		$form->add($snmpInterval);
	}

    /**
     * Sets the post data to the settings variables
     *
     * @param SettingsContainer $settings	The settings object
     * @param array $data					The posted data
     */
    public function setPostData(SettingsContainer &$settings, array $data)
    {
        $settings->snmp->enabled = isset($data['snmp-enabled']) && $data['snmp-enabled'] == 'on' ? '1' : '0';
        $settings->snmp->update_interval = $data['snmp-update-interval'];
    }
}