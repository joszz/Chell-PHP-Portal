<?php

namespace Chell\Models;

use WriteiniFile\ReadiniFile;
use WriteiniFile\WriteiniFile;

/**
 * This model represents a setting.
 * Used to substitute for a Settings model, when settings can't be retrieved from the db yet.
 *
 * @package Models
 */
class SettingsDefault
{
    public string $category = 'application';
    public string $section = 'general';

    public function __construct(public string $name, public string $value, public SettingsDefaultStorageType $type)
    {
    }

    /**
     * Saves only data for ini based settings.
     * Other types of default settings do not need to be stored.
     */
    public function save()
    {
        if ($this->type == SettingsDefaultStorageType::ini)
        {
            $data = ReadiniFile::get(APP_PATH . 'app/config/config.ini');
            $data['general']['debug'] = $this->value;
            (new WriteiniFile(APP_PATH . 'app/config/config.ini'))->create($data)->write();
        }
    }
}

enum SettingsDefaultStorageType
{
    case none;
    case db;
    case ini;
}