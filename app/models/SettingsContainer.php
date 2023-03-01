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
use Phalcon\Di\Exception as DiException;
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
    public function __construct(ConfigIni $config)
    {
        $this->addSection($dashboardSection = new SettingsSection('dashboard'));
        $this->addSection($generalSection = new SettingsSection('general'));
        $dashboardSection->addCategory(new SettingsCategory('dashboard', $generalSection));
        $generalSection->addCategory($applicationCategory = new SettingsCategory('application', $generalSection));

        $applicationCategory->addSetting(new Settings(['name' => 'version', 'value' => $this->getVersion(), 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::none]));
        $applicationCategory->addSetting(new Settings(['name' => 'debug', 'value' => $config->general->debug, 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::ini]));
        $applicationCategory->addSetting(new Settings(['name' => 'title', 'value' => 'Chell PHP Portal', 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::db]));
        $applicationCategory->addSetting(new Settings(['name' => 'background', 'value' => 'autobg', 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::db]));
        $applicationCategory->addSetting(new Settings(['name' => 'demo_mode', 'value' => '0', 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::db]));
        $applicationCategory->addSetting(new Settings(['name' => 'alert_timeout', 'value' => '5', 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::db]));
        $applicationCategory->addSetting(new Settings(['name' => 'items_per_page', 'value' => '10', 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::db]));
        $applicationCategory->addSetting(new Settings(['name' => 'check_device_states_interval', 'value' => '10', 'section' => 'general', 'category' => 'application', 'storagetype' => SettingsDefaultStorageType::db]));

        //todo: better way to check if DB is initialized
        try {
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
        catch (DiException $exception)
        {
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


    public function __get(string $name) : SettingsSection | SettingsCategory | null
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

            return null;
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
        return isset($this->_sections[$name]);
    }

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