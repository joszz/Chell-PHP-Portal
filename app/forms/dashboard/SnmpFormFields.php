<?php

namespace Chell\Forms\Dashboard;

use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Validation\Validator\Numericality;

class SnmpFormFields implements IDashboardFormFields
{
	/**
     * Adds fields to the form.
     */
	public function setFields($form)
	{
		$snmpEnabled = new Check('snmp-enabled');
		$snmpEnabled->setLabel('Enabled');
		$snmpEnabled->setAttributes([
			'checked' => $form->_config->snmp->enabled == '1' ? 'checked' : null,
			'data-toggle' => 'toggle',
			'data-onstyle' => 'success',
			'data-offstyle' => 'danger',
			'data-size' => 'small',
			'fieldset' => 'SNMP'
		]);

		$snmpInterval = new Numeric('snmp-update-interval');
		$snmpInterval->setLabel('SNMP interval')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control', 'fieldset' => 'end'])
			->setDefault($form->_config->snmp->updateInterval)
			->addValidator(new Numericality(['message' => 'Not a number']));

		$form->add($snmpEnabled);
		$form->add($snmpInterval);
	}

    public function setPostData(&$config, $data)
    {
        $config->snmp->enabled = isset($data['snmp-enabled']) && $data['snmp-enabled'] == 'on' ? '1' : '0';
        $config->snmp->updateInterval = $data['snmp-update-interval'];
    }
}