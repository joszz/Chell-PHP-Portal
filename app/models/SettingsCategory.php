<?php

namespace Chell\Models;

use Chell\Models\Settings;
use Chell\Models\SettingsDefault;
use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to setting categories.
 *
 * @package Models
 */
class SettingsCategory extends Model
{
    public function initialize()
    {
        $this->hasMany(
            'id',
            Settings::class,
            'settings_category_id',
            ['alias' => 'settings']
        );
    }
}