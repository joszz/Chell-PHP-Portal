<?php

namespace Chell\Models;

use Chell\Models\SettingsCategory;
use Phalcon\Mvc\Model\TransactionInterface;

/**
 * The model responsible for all actions related to setting categories.
 *
 * @package Models
 */
class SettingsSection
{
    private array $_categories = [];

    /**
     * Initializes a settings section.
     *
     * @param string $section    The section, such as general or dashboard.
     */
    public function __construct(public string $name)
    {
    }

    /**
     * Sets a Setting's value to the provided $value.
     *
     * @param string $name  The name of the Setting to set the value for.
     * @param string $value The value to set for the given setting.
     */
    public function __set(string $name, SettingsCategory $value)
    {
        $this->_categories[$name] = $value;
    }

    /**
     * Retrieves a setting's value by the provided setting $name.
     *
     * @param string $name  The name of the setting to retrieve the value for.
     */
    public function __get(string $name)
    {
        if (!isset($this->_categories[$name]))
        {
            $this->_categories[] = $category = new SettingsCategory($name, $this);
            return $category;
        }

        return $this->_categories[$name];
    }

    /**
     * Checks whether a setting with the given $name exists in the array of settings.
     *
     * @param string $name  The setting's name to check
     * @return bool         True if exists, otherwise false.
     */
    public function __isset(string $name) : bool
    {
        return isset($this->_categories[$name]);
    }

    /**
     * Calls save on all settings belonging to this category.
     */
    public function save(TransactionInterface $transaction)
    {
        foreach ($this->_categories as $category)
        {
            $category->save($transaction);
        }
    }

    /**
     * Adds a setting to the array of settings.
     *
     * @param object $setting     The setting to add.
     */
    public function addCategory(SettingsCategory $category)
    {
        $this->_categories[$category->name] = $category;
    }
}