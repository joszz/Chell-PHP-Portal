<?php

namespace Chell\Models;

use Exception;
use Chell\Models\SettingsCategory;
use Chell\Models\SettingsDefault;
use Chell\Models\SettingsDefaultStorageType;
use Phalcon\Mvc\Model;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

/**
 * The model responsible for all actions related to the settings object, containing all DB settings.
 *
 * @package Models
 */
class SettingsContainer
{
    /**
     * If DB is configured; get all settings from the database and add them to the SettingsContainer.
     * If DB is not configured; set some default settings which are required for Chell to work.
     */
    public function __construct(ConfigIni $config, bool $dbSet)
    {
        $this->application = new SettingsCategory();
        $this->application->version = new SettingsDefault(self::getVersion(), SettingsDefaultStorageType::none);
        $this->application->debug= new SettingsDefault($config->general->debug, SettingsDefaultStorageType::ini);
        $this->application->title = new SettingsDefault('Chell PHP Portal', SettingsDefaultStorageType::db);
        $this->application->background = new SettingsDefault('autobg', SettingsDefaultStorageType::db);
        $this->application->demo_mode= new SettingsDefault('0', SettingsDefaultStorageType::db);
        $this->application->alert_timeout = new SettingsDefault('5', SettingsDefaultStorageType::db);
        $this->application->items_per_page = new SettingsDefault('10', SettingsDefaultStorageType::db);
        $this->application->check_device_states_interval = new SettingsDefault('10', SettingsDefaultStorageType::db);

        if ($dbSet)
        {
            $categories = SettingsCategory::find(['order' => 'name']);

            foreach ($categories as $category)
            {
                if (!isset($this->{$category->name}))
                {
                    $this->{$category->name} = $category;
                }
                else if ($this->{$category->name}->getDirtyState() == Model::DIRTY_STATE_TRANSIENT)
                {
                    foreach ($this->{$category->name} as $name => $setting)
                    {
                        $category->{$name} = $setting;
                    }

                    $this->{$category->name} = $category;
                }
                foreach ($category->getSettings() as $setting)
                {
                    $this->{$category->name}->{$setting->name} = $setting;
                }
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
        require(__DIR__ . '/../../package.json');
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
     * Calls save on all sections that match $sectionName.
     *
     * @param string $sectionName   The name of the section to save.
     */
    public function save(string $sectionName)
    {
        $transactionManager = new TransactionManager();
        $transaction = $transactionManager->get();

        //$generalCategories = ['application', 'redis', 'imageproxy', 'duo', 'hibp'];
        try
        {
            foreach ($this as $category)
            {
                //if($sectionName === 'general' && in_array($category->ma,e, $generalCategories)
                foreach ($category as $name => $setting)
                {
                    if (($setting instanceof Settings || $setting instanceof SettingsDefault) && $setting->hasChanged('value'))
                    {
                        $setting->save($name, $category->id);
                    }
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