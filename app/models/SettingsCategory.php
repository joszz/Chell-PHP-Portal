<?php

namespace Chell\Models;

use Chell\Models\Settings;
/**
 * The model responsible for all actions related to setting categories.
 *
 * @package Models
 */
class SettingsCategory
{
    private array $_settings;

    public string $section;
    public string $category;

    /**
     * Initializes a settings category.
     *
     * @param string $section    The section, such as general or dashboard.
     * @param string $category   The category, such as application wide or plugin specific.
     */
    public function __construct(string $section, string $category){
        $this->section = $section;
        $this->category = $category;
    }

    public function __set(string $name, string $value)
    {
        $this->_settings[$name]->value = $value;
    }

    public function __get(string $name)
    {
        return $this->_settings[$name]->value;
    }

    public function __isset(string $name)
    {
        return isset($this->_settings[$name]);
    }

    public function save()
    {
        foreach ($this->_settings as $setting)
        {
            $setting->save();
        }
    }

    public function addSetting(Settings $setting)
    {
        $this->_settings[$setting->name] = $setting;
    }
}