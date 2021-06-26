<?php

namespace Chell\Models;

use Exception;
use Chell\Models\SettingsDefault;

/**
 * The model responsible for all actions related to setting categories.
 *
 * @package Models
 */
class SettingsCategory
{
    private array $_settings = [];

    public string $section;
    public string $category;

    /**
     * Initializes a settings category.
     *
     * @param string $section    The section, such as general or dashboard.
     * @param string $category   The category, such as application wide or plugin specific.
     */
    public function __construct(string $section, string $category)
    {
        $this->section = $section;
        $this->category = $category;
    }

    /**
     * Sets a Setting's value to the provided $value.
     *
     * @param string $name  The name of the Setting to set the value for.
     * @param ?string $value The value to set for the given setting.
     */
    public function __set(string $name, ?string $value)
    {
        if (!isset($this->_settings[$name]))
        {
            throw new Exception('Setting "' . $name . '" does not exist in setting category "' . $this->category . '"');
        }

        $this->_settings[$name]->value = $value;
    }

    /**
     * Retrieves a setting's value by the provided setting $name.
     *
     * @param string $name  The name of the setting to retrieve the value for.
     * @return string       The retrieved value.
     */
    public function __get(string $name)
    {
        if (!isset($this->_settings[$name]))
        {
            throw new Exception('Setting "' . $name . '" does not exist in setting category "' . $this->category . '"');
        }

        return $this->_settings[$name]->value;
    }

    /**
     * Checks whether a setting with the given $name exists in the array of settings.
     *
     * @param string $name  The setting's name to check
     * @return bool         True if exists, otherwise false.
     */
    public function __isset(string $name)
    {
        return isset($this->_settings[$name]);
    }

    /**
     * Calls save on all settings belonging to this category.
     */
    public function save()
    {
        foreach ($this->_settings as $setting)
        {
            $setting->save();
        }
    }

    /**
     * Adds a setting to the array of settings.
     *
     * @param object $setting     The setting to add.
     */
    public function addSetting(object $setting)
    {
        $this->_settings[$setting->name] = $setting;
    }
}