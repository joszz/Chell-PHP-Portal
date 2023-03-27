<?php

namespace Chell\Models;

use Chell\Models\SettingsCategory;
use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to settings.
 *
 * @package Models
 */
class Settings extends Model
{
    public function initialize()
    {
        $this->hasOne(
            'settings_category_id',
            SettingsCategory::class,
            'id',
            ['alias' => 'category']
        );
    }
}