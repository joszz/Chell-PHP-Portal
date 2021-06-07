<?php

namespace Chell\Models;

use Chell\Models\SettingsCategory;

class SettingsContainer
{
    private array $_categories;

    public function __get(string $name)
    {
        return $this->_categories[$name];
    }

    public function __isset(string $name)
    {
        return isset($this->_categories[$name]);
    }

    public function addCategory(SettingsCategory $category)
    {
        $this->_categories[$category->category] = $category;
    }

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