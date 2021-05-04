<?php

namespace Chell\Models;

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
     * @param bool $jsonDecode	Whether to JSON decode the output of the CURL call.
	 * @return array            List of VMs as anonymous objects.
	 */
	public function getVMs($jsonDecode = true)
	{
		$curl = $this->getCurl('VMs/GetVMs');
		$content = curl_exec($curl);
		curl_close($curl);

		return $jsonDecode ? json_decode($content) : $content;
	}

	/**
	 * Retrieves all sites using cURL and settings defined in config.ini.
	 *
     * @param bool $jsonDecode	Whether to JSON decode the output of the CURL call.
	 * @return object           List of sites as anonymous objects.
	 */
	public function getSites($jsonDecode = true)
	{
		$curl = $this->getCurl('Sites/GetSites');
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
	public function getVMState($state) : string
	{
		return $state == self::vmStateEnabed ? 'enabled' : 'disabled';
	}

	/**
	 * Gets a string representing the site state given an int.
	 *
	 * @param number $state	The site state represented as a number.
	 * @return string		The site state represented as a string.
	 */
	public function getSiteState($state) : string
	{
		return $state == self::siteStateEnabed ? 'enabled' : 'disabled';
	}

	/**
	 * For the given VM with the name of $vmName, set the state to $state.
	 *
	 * @param string $vmName    The name of the VM to set the state for.
	 * @param number $state     The state to set the VM to.
	 * @return object           The response returned as an anonymous object.
	 */
	public function toggleVMState($vmName, $state)
	{
		$curl = $this->getCurl('VMs/ToggleState?vmName=' . urlencode($vmName) . '&state=' . $state);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		return $content;
	}

	/**
	 * For the given site with the name of $siteName, set the state to $state.
	 *
	 * @param string $siteName  The name of the site to set the state for.
	 * @param number $state     The state to set the VM to.
	 * @return object           The response returned as an anonymous object.
	 */
	public function toggleSiteState($siteName, $state)
	{
		$url = 'Sites/' . ($state == self::siteStateEnabed ? 'Start' : 'Stop') . 'Site?sitename=' . urlencode($siteName);
		$curl = $this->getCurl($url);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		return $content;
	}

	/**
	 * Retrieves the default cURL object to be used in this model, setting some defaults.
	 *
	 * @param string $url       The URL to be appended to the base URL of HyperVAdmin ($config->hypervadmin->URL).
	 * @return \CurlHandle|bool	The cURL object to be used to query HyperVAdmin.
	 */
	private function getCurl($url)
	{
		$curl = curl_init($this->_config->hypervadmin->URL . $url);

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_USERPWD => $this->_config->hypervadmin->username . ':' . $this->_config->hypervadmin->password,
		]);

		return $curl;
	}
}