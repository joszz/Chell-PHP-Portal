<?php

namespace Chell\Models;

use Exception;
use stdClass;
use Chell\Models\SettingsCategory;

/**
 * The model responsible for all actions related to the settings object, containing all DB settings.
 *
 * @package Models
 */
class SettingsContainer
{
    private array $_categories = [];

    public function __construct($dbSet)
    {
        $this->_categories = ['application' => new SettingsCategory('general', 'application')];

        if ($dbSet)
        {
            $settings = Settings::find(['order' => 'category']);

            foreach ($settings as $setting)
            {
                if (!isset($this->{$setting->category}))
                {
                    $this->addCategory(new SettingsCategory($setting->section, $setting->category));
                }

                $this->{$setting->category}->addSetting($setting);
            }
        }
        else
        {
            $this->addDefaultSetting('title', 'Chell PHP Portal');
            $this->addDefaultSetting('version', '0');
            $this->addDefaultSetting('background', 'autobg');
            $this->addDefaultSetting('demo_mode', '0');
            $this->addDefaultSetting('alert_timeout', '5');
        }
    }

    private function addDefaultSetting($name, $value)
    {
        $setting = new stdClass();
        $setting->value = $value;

        $this->application->$name = $setting;
    }

    /**
     * Retrieves a setting category.
     *
     * @param string $name  The name of the setting category.
     * @return mixed
     */
    public function __get(string $name) : SettingsCategory
    {
        if (!isset($this->_categories[$name]))
        {
            throw new Exception('Category "' . $name . '" does not exist');
        }

        return $this->_categories[$name];
    }

    /**
     * Whether or not a setting category exists with the given name.
     *
     * @param string $name  The name of the setting category.
     * @return bool         True if exists, otherwise false.
     */
    public function __isset(string $name) : bool
    {
        return isset($this->_categories[$name]);
    }

    /**
     * Adds a new SettingCategory to the array of categoreis.
     *
     * @param SettingsCategory $category    The category to add.
     */
    public function addCategory(SettingsCategory $category)
    {
        $this->_categories[$category->category] = $category;
    }

    /**
     * Calls save on all SettingsCategories that match the provided $secion.
     *
     * @param string $section   The categories with matching section to save.
     */
    public function save(string $section)
    {
        foreach ($this->_categories as $category)
        {
            if ($category->section == $section)
            {
                $category->save();
            }
        }
    }
}