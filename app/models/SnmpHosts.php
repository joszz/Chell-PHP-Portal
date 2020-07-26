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
        //$fulltree = $session->walk(".");
        //die(var_dump($fulltree));

        foreach($this->getRecords(['order' => '-position DESC']) as $record)
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
                            'label'             => $oidLabel . '(' . $record->label . ')',
                            'values'            => [ $oidValue ],
                            'divisor'           => $record->divisor,
                            'divisor_decimals'  => $record->divisor_decimals,
                            'value_unit'        => $record->value_unit
                        ];
                    }
                    else
                    {
                        $values[$record->id] =[
                            'label'             => $record->label,
                            'values'            => [ $oidValue ],
                            'divisor'           => $record->divisor,
                            'divisor_decimals'  => $record->divisor_decimals,
                            'value_unit'        => $record->value_unit
                        ];
                    }
                }
            }
        }

        $session->close();

        return $values;
    }

    public function formatOidValues($record)
    {
        $formattedValues = [];

        foreach ($record['values'] as $value)
        {
            list($type, $value) = explode(': ', $value, 2);
            $type = strtolower($type);

            if ($type == 'timeticks')
            {
                $value = intval(trim(explode(')', $value, 2)[0], '('));

            }
            else if ($type == 'integer')
            {
                $value = intval($value);
            }

            if(!empty($record['divisor']))
            {
                $value = round($value / $record['divisor'], $record['divisor_decimals']);
            }

            if(!empty($record['value_unit']))
            {
                $value .= ' ' . $record['value_unit'];
            }

            $formattedValues[] = trim($value, '"');
        }

        return [$type, $formattedValues];
    }
}
