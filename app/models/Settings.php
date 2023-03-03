<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to settings.
 *
 * @package Models
 */
class Settings extends Model
{
    /**
     * Saves only data for ini based settings.
     * Other types of default settings do not need to be stored.
     */
    public function save(): bool
    {
        unset($this->type);
        return parent::save();
    }
}