<?php

namespace Chell\Forms;

use Chell\Models\SnmpHosts;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;

/**
 * The form responsible for adding new SNMP records.
 *
 * @package Forms
 */
class SettingsSnmpRecordForm extends SettingsBaseForm
{
	/**
	 * Add all fields to the form and set form specific attributes.
	 */
	public function initialize($entity)
	{
		$label = new Text('label');
		$label->setLabel('Label')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control']);

		$host = new Select(
			'snmp_host_id' ,
			SnmpHosts::find(),
			['using' => ['id', 'name']]
		);
		$host->setLabel('Host');

		$labelOID = new Text('label_oid');
		$labelOID->setLabel('Label OID')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control']);

		$valueOID = new Text('value_oid');
		$valueOID->setLabel('Value OID')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control'])
			->addValidator(new PresenceOf(['message' => $this->translator->validation['required']]));

		$showDasboard = new Check('show_dashboard', ['value' => '1']);
		$showDasboard->setLabel('Show on dashboard')
			->setAttributes([
				'data-toggle' => 'toggle',
				'data-onstyle' => 'success',
				'data-offstyle' => 'danger',
				'data-size' => 'small'
		]);

		$position = new Numeric('position');
		$position->setLabel('Position')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->addValidator(new Numericality(['message' => $this->translator->validation['not-a-number']]));

		$divisor = new Numeric('divisor');
		$divisor->setLabel('Divisor')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->addValidator(new Numericality(['message' => $this->translator->validation['not-a-number']]));

		$divisor_decimals = new Numeric('divisor_decimals');
		$divisor_decimals->setLabel('Divisor decimals')
			->setFilters(['striptags', 'int'])
			->setAttributes(['class' => 'form-control'])
			->addValidator(new Numericality(['message' => $this->translator->validation['not-a-number']]));

		$value_unit = new Text('value_unit');
		$value_unit->setLabel('Value unit')
			->setFilters(['striptags', 'string'])
			->setAttributes(['class' => 'form-control']);

        $groupValue = new Select(
            'group_value',
			SnmpHosts::findFirst(['conditions' => 'id = ?1', 'bind'=> [1 => $entity->snmp_host_id]])
				->getRecords(['columns' => 'id, CONCAT(id, " - ", label) AS id_label']),
            [
                'using'         => ['id', 'id_label'],
                'useEmpty'      => true,
                'emptyText'     => 'None',
                'emptyValue'    => 0
            ]
        );
        $groupValue->setLabel('Group value');
		
		$this->add($label);
		$this->add($host);
		$this->add($labelOID);
		$this->add($valueOID);
		$this->add($position);
		$this->add($divisor);
		$this->add($divisor_decimals);
		$this->add($value_unit);
		$this->add($groupValue);
		$this->add($showDasboard);
	}
}