<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to HyperVAdmin.
 *
 * @package Models
 */
class HyperVAdmin extends Model
{
    const vmStates = array(2 => 'enabled', 3 => 'disabled');

    /**
     * Retrieves all VMs using cURL and settings defined in config.ini.
     *
     * @param object $config    The configuration file to use.
     * @return array            List of VMs as anonymous objects.
     */
    public static function getVMs($config)
    {
        $curl = self::getCurl('VMs/GetVMs', $config);
		$content = json_decode(curl_exec($curl));
        curl_close($curl);

		return $content;
    }

    /**
     * Gets a string representing the state given an int.
     *
     * @param number $state     The VM state represented as a number.
     * @return string           The VM state represented as a string.
     */
    public function getVMState($state)
    {
        if(isset(self::vmStates[$state])) {
            return self::vmStates[$state];
        }

        return 'disabled';
    }

    /**
     * For the given VM with the name of $vmName, set the state to $state.
     *
     * @param string $vmName    The name of the VM to set the state for.
     * @param number $state     The state to set the VM to.
     * @param object $config    The configuration file to use.
     * @return object           The response returned as an anonymous object.
     */
    public static function toggleVMState($vmName, $state, $config)
    {
        $curl = self::getCurl('VMs/ToggleState?vmName=' . $vmName . '&state=' . $state, $config);
		$content = json_decode(curl_exec($curl));
        curl_close($curl);

		return $content;
    }

    /**
     * Retrieves all sites using cURL and settings defined in config.ini.
     *
     * @param object $config    The configuration file to use.
     * @return object           List of sites as anonymous objects.
     */
    public static function getSites($config)
    {
        $curl = self::getCurl('Sites/GetSites', $config);
		$content = json_decode(curl_exec($curl));
        curl_close($curl);

		return $content;
    }

    /**
     * Retrieves the default cURL object to be used in this model, setting some defaults.
     *
     * @param string $url       The URL to be appended to the base URL of HyperVAdmin ($config->hypervadmin->URL).
     * @param object $config    The configuration file to use.
     * @return object           The cURL object to be used to query HyperVAdmin.
     */
    private static function getCurl($url, $config)
    {
        $curl = curl_init($config->hypervadmin->URL . $url);
		curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $config->hypervadmin->username . ':' . $config->hypervadmin->password,
            CURLOPT_USERAGENT => 'Mozilla/5.001 (windows; U; NT4.0; en-US; rv:1.0) Gecko/25250101'
        ));

        return $curl;
    }
}