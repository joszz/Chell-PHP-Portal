<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 */
class SnmpRecords extends Model
{

    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->belongsTo(
            'snmp_host_id',
            'Chell\Models\SnmpHosts',
            'id',
            ['alias' => 'host']
        );
    }
}
