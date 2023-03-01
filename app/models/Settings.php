<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;
use WriteiniFile\ReadiniFile;
use WriteiniFile\WriteiniFile;

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
        if ($this->type == SettingsDefaultStorageType::ini)
        {
            $data = ReadiniFile::get(APP_PATH . 'app/config/config.ini');
            $data['general']['debug'] = $this->value;
            (new WriteiniFile(APP_PATH . 'app/config/config.ini'))->create($data)->write();
            return true;
        }
        else
        {
            unset($this->type);
            return parent::save();
        }
    }
}

enum SettingsDefaultStorageType
{
    case none;
    case db;
    case ini;
}