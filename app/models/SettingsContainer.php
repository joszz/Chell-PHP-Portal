<?php

namespace Chell\Models;

use ArrayObject;
use Exception;
use Iterator;
use IteratorAggregate;
use Chell\Models\Settings;
use Chell\Models\SettingsCategory;
use Chell\Models\SettingsDefaultStorageType;
use Chell\Models\SettingsSection;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

/**
 * The model responsible for all actions related to the settings object, containing all DB settings.
 *
 * @package Models
 */
class SettingsContainer implements IteratorAggregate
{
    private array $_sections = [];

    /**
     * If DB is configured; get all settings from the database and add them to the SettingsContainer.
     * If DB is not configured; set some default settings which are required for Chell to work.
     */
    public function __construct(ConfigIni $config, bool $dbSet)
    {
        $this->addSection($dashboardSection = new SettingsSection('dashboard'));
        $this->addSection($generalSection = new SettingsSection('general'));
        $dashboardSection->addCategory(new SettingsCategory('dashboard', $generalSection));
        $generalSection->addCategory($applicationCategory = new SettingsCategory('application', $generalSection));

        $applicationCategory->addSetting(new SettingsDefault('version', self::getVersion(), SettingsDefaultStorageType::none));
        $applicationCategory->addSetting(new SettingsDefault('debug', $config->general->debug, SettingsDefaultStorageType::ini));
        $applicationCategory->addSetting(new SettingsDefault('title', 'Chell PHP Portal', SettingsDefaultStorageType::db));
        $applicationCategory->addSetting(new SettingsDefault('background', 'autobg', SettingsDefaultStorageType::db));
        $applicationCategory->addSetting(new SettingsDefault('demo_mode', '0', SettingsDefaultStorageType::db));
        $applicationCategory->addSetting(new SettingsDefault('alert_timeout', '5', SettingsDefaultStorageType::db));
        $applicationCategory->addSetting(new SettingsDefault('items_per_page', '10', SettingsDefaultStorageType::db));
        $applicationCategory->addSetting(new SettingsDefault('check_device_states_interval', '10', SettingsDefaultStorageType::db));

        if ($dbSet)
        {
            $settings = Settings::find(['order' => 'category']);

            foreach ($settings as $setting)
            {
                if (!isset($this->{$setting->section}->{$setting->category}))
                {
                    $this->{$setting->section}->addCategory(new SettingsCategory($setting->category, $this->{$setting->section}));
                }

                $this->{$setting->section}->{$setting->category}->addSetting($setting);
            }
        }
    }

    /**
     * Retrieves version from package.json
     *
     * @return string   Version
     */
    private static function getVersion() : string
    {
        ob_start();
        require_once(__DIR__ . '/../../package.json');
        return json_decode(ob_get_clean())->version;
    }

    /**
     * Retrieves the latest migration version AKA the highest version directory number in the migrations directory.
     *
     * @return string   The version used for DB migration.
     */
    public static function getMigrationVersion() : string
    {
        $version = self::getVersion();

        if (!is_dir(__DIR__ . '/../migrations/' . $version))
        {
            $allMigrations = glob(__DIR__ . '/../migrations/*', GLOB_ONLYDIR);
            rsort($allMigrations);
            $version = basename($allMigrations[0]);
        }

        return $version;
    }

    /**
     * Retrieve a SettingsSection.
     * If $name is not found in array of sections, loop through all sections and try to get a SettingsCategory by the provided $name.
     *
     * @param string $name                         The name of the SettingsSection or SettingsCategory to retrieve.
     * @return SettingsSection|SettingsCategory    Either SettingsSection or SettingsCategory.
     */
    public function __get(string $name) : SettingsSection | SettingsCategory
    {
        if (!isset($this->_sections[$name]))
        {
            foreach ($this->_sections as $section)
            {
                if (isset($section->{$name}))
                {
                    return $section->{$name};
                }
            }

            return new SettingsCategory($name, null);
        }

        return $this->_sections[$name];
    }

    /**
     * Whether or not a setting category exists with the given name.
     *
     * @param string $name  The name of the setting category.
     * @return bool         True if exists, otherwise false.
     */
    public function __isset(string $name) : bool
    {
        if (!isset($this->_sections[$name]))
        {
            foreach ($this->_sections as $section)
            {
                if (isset($section->{$name}))
                {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Retrieves the iterator so you can loop over this object.
     * @return Iterator
     */
    public function getIterator() : Iterator
    {
        return (new ArrayObject($this->_sections))->getIterator();
    }

    /**
     * Adds a new SettingCategory to the array of categoreis.
     *
     * @param SettingsCategory $category    The category to add.
     */
    public function addSection(SettingsSection $section)
    {
        $this->_sections[$section->name] = $section;
    }

    /**
     * Calls save on all sections that match $sectionName.
     *
     * @param string $sectionName   The name of the section to save.
     */
    public function save(string $sectionName)
    {
        $transactionManager = new TransactionManager();
        $transaction = $transactionManager->get();

        try
        {
            foreach ($this->_sections as $section)
            {
                if ($section->name == $sectionName)
                {
                    $section->save($transaction);
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

    /**
     * Retrieves the current domain with the protocol (either HTTP or HTTPS).
     *
     * @param bool $urlEncode   Whether or not to urlencode the output.
     * @return string           The protocol + domain.
     */
    public function getDomainWithProtocol(bool $urlEncode = false)
    {
        $protocol = 'http://';

        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }

        $domainWithProtocol = $protocol . $_SERVER['SERVER_NAME'];
        return $urlEncode ? urlencode($domainWithProtocol) : $domainWithProtocol;
    }
}