<?php

namespace Chell\Models;


/**
 * The model responsible for all actions related to setting categories.
 *
 * @package Models
 */
class SettingsCategory
{
    private $_settings;

    public $section;
    public $category;

    public function __construct($section, $category){
        $this->section = $section;
        $this->category = $category;
    }

    public function __set($name, $value)
    {
        $this->_settings[$name]->value = $value;
    }

    public function __get($name)
    {
        return $this->_settings[$name]->value;
    }

    public function __isset($name)
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

    public function addSetting($setting)
    {
        $this->_settings[$setting->name] = $setting;
    }
}