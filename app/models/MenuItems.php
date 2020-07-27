<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to menu items.
 *
 * @package Models
 */
class MenuItems extends Model
{
    /**
     * Sets the database relations
     */
    public function initialize()
    {
        $this->belongsTo(
            'menu_id',
            'Chell\Models\Menus',
            'id'
        );

        $this->belongsTo(
            'device_id',
            'Chell\Models\Devices',
            'id',
            ['alias' => 'device']
        );
    }
}