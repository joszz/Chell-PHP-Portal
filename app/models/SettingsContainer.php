<?php

namespace Chell\Models;

class SettingsContainer
{
    private $_categories;

    public function __get($name)
    {
        return $this->_categories[$name];
    }

    public function __isset($name)
    {
        return isset($this->_categories[$name]);
    }

    public function addCategory($category)
    {
        $this->_categories[$category->category] = $category;
    }

    public function save($section)
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