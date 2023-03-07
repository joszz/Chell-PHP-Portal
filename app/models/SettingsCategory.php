<?php

namespace Chell\Models;

use Chell\Models\Settings;
use Chell\Models\SettingsDefault;
use Chell\Models\SettingsSection;
use Phalcon\Mvc\Model\TransactionInterface;

/**
 * The model responsible for all actions related to setting categories.
 *
 * @package Models
 */
class SettingsCategory
{
    private array $_settings = [];

    /**
     * Initializes a settings category.
     *
     * @param string $category   The category, such as application wide or plugin specific.
     */
    public function __construct(public string $name, public ?SettingsSection $section)
    {
    }

    /**
     * Sets a Setting's value to the provided $value.
     *
     * @param string $name  The name of the Setting to set the value for.
     * @param string $value The value to set for the given setting.
     */
    public function __set(string $name, ?string $value)
    {
        if (!isset($this->_settings[$name]))
        {
            $this->_settings[$name] = new Settings(['name' => $name, 'category' => $this->name, 'section' => $this->section?->name, 'value' => $value]);
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
        if (empty($this->_settings[$name]?->value))
        {
            return null;
        }

        return $this->_settings[$name]->value;
    }

    /**
     * Checks whether a setting with the given $name exists in the array of settings.
     *
     * @param string $name  The setting's name to check
     * @return bool         True if exists, otherwise false.
     */
    public function __isset(string $name) : bool
    {
        return isset($this->_settings[$name]);
    }

    /**
     * Calls save on all settings belonging to this category.
     */
    public function save(TransactionInterface $transaction)
    {
        foreach ($this->_settings as $setting)
        {
            if ($setting instanceof Settings)
            {
                $setting->setTransaction($transaction);
            }

            $setting->save();
        }
    }

    /**
     * Adds a setting to the array of settings.
     *
     * @param object $setting     The setting to add.
     */
    public function addSetting(Settings | SettingsDefault $setting)
    {
        $this->_settings[$setting->name] = $setting;
    }
}