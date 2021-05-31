<?php

namespace Chell\Forms\FormFields\Dashboard;

use Chell\Forms\FormFields\IFormFields;
use Chell\Forms\Validators\PresenceOfConfirmation;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Validation\Validator\Numericality;

class SnmpFormFields implements IFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
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
     * Sets the post data to the config variables
     *
     * @param object $config	The config object, representing config.ini
     * @param array $data		The posted data
     */
    public function setPostData(&$settings, $data)
    {
        $settings->snmp->enabled = isset($data['snmp-enabled']) && $data['snmp-enabled'] == 'on' ? '1' : '0';
        $settings->snmp->update_interval = $data['snmp-update-interval'];
    }
}