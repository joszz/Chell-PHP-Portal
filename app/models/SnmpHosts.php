<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 */
class SnmpHosts extends Model
{
    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->hasMany(
            'id',
            'Chell\Models\SnmpRecords',
            'snmp_host_id',
            array('alias' => 'records')
        );
    }

    public function getValues($showDashboard)
    {
        $values = [];
        $oidLabelCache = [];
        $session = new \SNMP(constant('SNMP::VERSION_' . $this->version), $this->ip, $this->community);

        foreach($this->records as $record)
        {
            if ($record->show_dashboard == $showDashboard)
            {
                $oidValue = $session->get($record->value_oid);

                if (!empty($record->group_value))
                {
                    $values[$record->group_value]['values'][] = $oidValue;
                }
                else
                {
                    if (!empty($record->label_oid))
                    {
                        if (!array_key_exists($record->label_oid, $oidLabelCache))
                        {
                            $labelParts = explode(': ', $session->get($record->label_oid), 2);
                            $oidLabelCache[$record->label_oid] = trim($labelParts[1], '"');
                        }

                        $oidLabel = $oidLabelCache[$record->label_oid];
                        $values[$record->id] = [
                            'label'     => $oidLabel . '(' . $record->label . ')',
                            'values'     => [ $oidValue ]
                        ];
                    }
                    else
                    {
                        $values[$record->id] =[
                            'label' => $record->label,
                            'values' => [ $oidValue ]
                        ];
                    }
                }
            }
        }

        return $values;
    }

    public function getOidValues($values){
        $formattedValues = [];

        foreach ($values as $value)
        {
            list($type, $value) = explode(': ', $value, 2);

            if ($type == 'Timeticks')
            {
                $value = intval(trim(explode(')', $value, 2)[0], '(')) / 100;
            }

            $formattedValues[] = trim($value, '"');
        }

        return [$type, $formattedValues];
    }
}
