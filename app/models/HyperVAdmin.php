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
	const vmStateEnabed = 2;
	const vmStateDisabed = 3;
	const siteStateEnabed = 1;
	const siteStateDisabed = 3;

	/**
	 * Retrieves all VMs using cURL and settings defined in config.ini.
	 *
	 * @param object $config    The configuration file to use.
	 * @return array            List of VMs as anonymous objects.
	 */
	public static function getVMs($config, $jsonDecode = true)
	{
		$curl = self::getCurl('VMs/GetVMs', $config);
		$content = curl_exec($curl);
		curl_close($curl);

		return $jsonDecode ? json_decode($content) : $content;
	}

	/**
	 * Retrieves all sites using cURL and settings defined in config.ini.
	 *
	 * @param object $config    The configuration file to use.
	 * @return object           List of sites as anonymous objects.
	 */
	public static function getSites($config, $jsonDecode = true)
	{
		$curl = self::getCurl('Sites/GetSites', $config);
		$content = curl_exec($curl);
		curl_close($curl);

		return $jsonDecode ? json_decode($content) :  $content;
	}

	/**
	 * Gets a string representing the VM state given an int.
	 *
	 * @param number $state     The VM state represented as a number.
	 * @return string           The VM state represented as a string.
	 */
	public function getVMState($state)
	{
		return $state == self::vmStateEnabed ? 'enabled' : 'disabled';
	}

	/**
	 * Gets a string representing the site state given an int.
	 *
	 * @param number $state	The site state represented as a number.
	 * @return string		The site state represented as a string.
	 */
	public function getSiteState($state)
	{
		return $state == self::siteStateEnabed ? 'enabled' : 'disabled';
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
		$curl = self::getCurl('VMs/ToggleState?vmName=' . urlencode($vmName) . '&state=' . $state, $config);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		return $content;
	}

	/**
	 * For the given site with the name of $siteName, set the state to $state.
	 *
	 * @param string $siteName  The name of the site to set the state for.
	 * @param number $state     The state to set the VM to.
	 * @param object $config    The configuration file to use.
	 * @return object           The response returned as an anonymous object.
	 */
	public static function toggleSiteState($siteName, $state, $config)
	{
		$url = 'Sites/' . ($state == self::siteStateEnabed ? 'Start' : 'Stop') . 'Site?sitename=' . urlencode($siteName);
		$curl = self::getCurl($url, $config);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		return $content;
	}

	/**
	 * Retrieves the default cURL object to be used in this model, setting some defaults.
	 *
	 * @param string $url       The URL to be appended to the base URL of HyperVAdmin ($config->hypervadmin->URL).
	 * @param object $config    The configuration file to use.
	 * @return resource			The cURL object to be used to query HyperVAdmin.
	 */
	private static function getCurl($url, $config)
	{
		$curl = curl_init($config->hypervadmin->URL . $url);

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_USERPWD => $config->hypervadmin->username . ':' . $config->hypervadmin->password,
		]);

		return $curl;
	}
}