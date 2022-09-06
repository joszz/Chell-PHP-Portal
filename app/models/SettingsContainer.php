<?php

namespace Chell\Models;

use ArrayObject;
use Exception;
use IteratorAggregate;
use Chell\Models\SettingsDefault;
use Chell\Models\SettingsCategory;
use Chell\Models\SettingsDefaultStorageType;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Di\Exception as DiException;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

/**
 * The model responsible for all actions related to the settings object, containing all DB settings.
 *
 * @package Models
 */
class SettingsContainer implements IteratorAggregate
{
    private array $_categories = [];

    /**
     * If DB is configured; get all settings from the database and add them to the SettingsContainer.
     * If DB is not configured; set some default settings which are required for Chell to work.
     */
    public function __construct(ConfigIni $config)
    {
        $this->_categories = ['application' => new SettingsCategory('general', 'application')];
        $this->application->addSetting(new SettingsDefault('version', $this->getVersion(), SettingsDefaultStorageType::none));
        $this->application->addSetting(new SettingsDefault('debug', $config->general->debug, SettingsDefaultStorageType::ini));

        try {
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
        catch (DiException $exception)
        {
            $this->application->addSetting(new SettingsDefault('title', 'Chell PHP Portal', SettingsDefaultStorageType::db));
            $this->application->addSetting(new SettingsDefault('background', 'autobg', SettingsDefaultStorageType::db));
            $this->application->addSetting(new SettingsDefault('demo_mode', '0', SettingsDefaultStorageType::db));
            $this->application->addSetting(new SettingsDefault('alert_timeout', '5', SettingsDefaultStorageType::db));
        }
    }

    /**
     * Retrieves version from package.json
     *
     * @return string   Version
     */
    private function getVersion() : string
    {
        ob_start();
        require_once(APP_PATH . 'package.json');
        return json_decode(ob_get_clean())->version;
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

    public function getIterator()
    {
        return (new ArrayObject($this->_categories))->getIterator();
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
        $transactionManager = new TransactionManager();
        $transaction = $transactionManager->get();

        try
        {
            foreach ($this->_categories as $category)
            {
                if ($category->section == $section)
                {
                    $category->save($transaction);
                }
            }

            $transaction->commit();
        }
        catch (Exception $exception)
        {
            $transaction->rollback();
            throw($exception);
        }
    }
}