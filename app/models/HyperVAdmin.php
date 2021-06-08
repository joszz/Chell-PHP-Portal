<?php

namespace Chell\Models;

use CurlHandle;
use Chell\Models\Devices;

/**
 * The model responsible for all actions related to HyperVAdmin.
 *
 * @package Models
 */
class HyperVAdmin extends BaseModel
{
	const vmStateEnabed = 2;
	const vmStateDisabed = 3;
	const siteStateEnabed = 1;
	const siteStateDisabed = 3;

	/**
	 * Retrieves all VMs using cURL and settings defined in config.ini.
	 *
     * @param Devices $device	The device to do the action for.
     * @param bool $jsonDecode	Whether to JSON decode the output of the CURL call.
	 * @return array            List of VMs as anonymous objects.
	 */
	public function getVMs(Devices $device, bool $jsonDecode = true)
	{
		$curl = $this->getCurl($device, 'VMs/GetVMs');
		$content = curl_exec($curl);
		curl_close($curl);

		return $jsonDecode ? json_decode($content) : $content;
	}

	/**
	 * Retrieves all sites using cURL and settings defined in config.ini.
	 *
     * @param Devices $device	The device to do the action for.
     * @param bool $jsonDecode	Whether to JSON decode the output of the CURL call.
	 * @return object           List of sites as anonymous objects.
	 */
	public function getSites(Devices $device, bool $jsonDecode = true)
	{
		$curl = $this->getCurl($device, 'Sites/GetSites');
		$content = curl_exec($curl);
		curl_close($curl);

		return $jsonDecode ? json_decode($content) :  $content;
	}

	/**
	 * Gets a string representing the VM state given an int.
	 *
     * @param int $state		The VM state represented as a number.
	 * @return string           The VM state represented as a string.
	 */
	public function getVMState(int $state) : string
	{
		return $state == self::vmStateEnabed ? 'enabled' : 'disabled';
	}

	/**
	 * Gets a string representing the site state given an int.
	 *
     * @param int $state	The site state represented as a number.
	 * @return string		The site state represented as a string.
	 */
	public function getSiteState(int $state) : string
	{
		return $state == self::siteStateEnabed ? 'enabled' : 'disabled';
	}

	/**
	 * For the given VM with the name of $vmName, set the state to $state.
	 *
     * @param Devices $device	The device to do the action for.
	 * @param string $vmName    The name of the VM to set the state for.
     * @param int $state		The state to set the VM to.
	 * @return object           The response returned as an anonymous object.
	 */
	public function toggleVMState(Devices $device, string $vmName, int $state)
	{
		$curl = $this->getCurl($device, 'VMs/ToggleState?vmName=' . urlencode($vmName) . '&state=' . $state);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		return $content;
	}

	/**
	 * For the given site with the name of $siteName, set the state to $state.
	 *
     * @param Devices $device	The device to do the action for.
	 * @param string $siteName  The name of the site to set the state for.
     * @param int $state		The state to set the VM to.
	 * @return object           The response returned as an anonymous object.
	 */
	public function toggleSiteState(Devices $device, string $siteName, int $state)
	{
		$url = 'Sites/' . ($state == self::siteStateEnabed ? 'Start' : 'Stop') . 'Site?sitename=' . urlencode($siteName);
		$curl = $this->getCurl($device, $url);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		return $content;
	}

	/**
	 * Retrieves the default cURL object to be used in this model, setting some defaults.
	 *
     * @param Devices $device	The device to do the action for.
	 * @param string $url       The URL to be appended to the base URL of HyperVAdmin ($config->hypervadmin->URL).
	 * @return CurlHandle|bool	The cURL object to be used to query HyperVAdmin.
	 */
	private function getCurl(Devices $device, string $url)
	{
		$curl = curl_init($device->hypervadmin_url . $url);

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_USERPWD => $device->hypervadmin_user . ':' . $device->hypervadmin_password,
		]);

		return $curl;
	}
}