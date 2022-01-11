<?php

namespace Chell\Models;

use Chell\Models\Devices;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to HyperVAdmin.
 *
 * @package Models
 * @suppress PHP2414
 */
class HyperVAdmin extends BaseModel
{
	const vmStateEnabed = 2;
	const vmStateDisabed = 3;
	const siteStateEnabed = 1;
	const siteStateDisabed = 3;

	/**
	 * Retrieves all VMs using Guzzle and settings defined in config.ini.
	 *
     * @param Devices $device	The device to do the action for.
     * @param bool $jsonDecode	Whether to JSON decode the output of the Guzzle call.
	 * @return array            List of VMs as anonymous objects.
	 */
	public function getVMs(Devices $device, bool $jsonDecode = true)
	{
		$content = $this->getHttpClient($device, '/VMs/GetVMs')->getBody();
		return $jsonDecode ? json_decode($content) : $content;
	}

	/**
     * Retrieves all sites using Guzzle and settings defined in config.ini.
	 *
     * @param Devices $device	The device to do the action for.
     * @param bool $jsonDecode	Whether to JSON decode the output of the Guzzle call.
	 * @return object           List of sites as anonymous objects.
	 */
	public function getSites(Devices $device, bool $jsonDecode = true)
	{
		$content = $this->getHttpClient($device, '/Sites/GetSites')->getBody();
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
		return $this->getHttpClient($device, '/VMs/ToggleState?vmName=' . urlencode($vmName) . '&state=' . $state)->getBody();
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
		$url = '/Sites/' . ($state == self::siteStateEnabed ? 'Start' : 'Stop') . 'Site?sitename=' . urlencode($siteName);
		return $this->getHttpClient($device, $url)->getBody();
	}

	/**
     * Gets the ResponseInterface to be used to invoke the HyperVAdmin API.
	 *
     * @param Devices $device		The device to do the action for.
	 * @param string $url			The URL to be appended to the base URL of HyperVAdmin ($config->hypervadmin->URL).
     * @return ResponseInterface    The ResponseInterface to call the API with.
	 */
    private function getHttpClient(Devices $device, string $url) : ResponseInterface
    {
        $client = new Client();
        return $client->request('GET', $device->hypervadmin_url . $url,
			['auth' => [$device->hypervadmin_user , $device->hypervadmin_password]]);
    }
}