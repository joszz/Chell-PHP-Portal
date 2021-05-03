<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 */
class Speedtest extends BaseModel
{
	private $ipAddress = '', $isp = '', $clientLocaction, $serverLocation, $distance;

	public $ip;
	public $ispinfo;
	public $extra;
	public $ua;
	public $lang;
	public $dl;
	public $ul;
	public $ping;
	public $jitter;
	public $log;

	/**
     * Get's ISP IP and name
     *
     * @param object $config	The config object representing config.ini.
     * @return string			The ISP IP and name as a concatenated string.
     */
	public function getIPAddress()
	{
		$this->setIPAddress();

		if ($this->ipAddress == '::1') {
			return $this->ipAddress . ' - localhost IPv6 access';
		}
		if (stripos($this->ipAddress, 'fe80:') === 0) {
			return $this->ipAddress . ' - link-local IPv6 access';
		}
		if (strpos($this->ipAddress, '127.') === 0) {
			return $this->ipAddress . ' - localhost IPv4 access';
		}
		if (strpos($this->ipAddress, '10.') === 0 || strpos($this->ipAddress, '192.168.') === 0) {
			return $this->ipAddress . ' - private IPv4 access';
		}
		if (preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $this->ipAddress) === 1) {
			return $this->ipAddress . ' - private IPv4 access';
		}
		if (strpos($this->ipAddress, '169.254.') === 0) {
			return $this->ipAddress . ' - link-local IPv4 access';
		}

		if (isset($_GET['isp']))
		{
			$this->setISPDetails();

			if (isset($_GET['distance']) && $this->clientLocaction && $this->serverLocation)
			{
				$this->distance($this->clientLocaction[0], $this->clientLocaction[1], $this->serverLocation[0], $this->serverLocation[1]);

				if ($_GET['distance'] == 'mi')
				{
					$this->distance /= 1.609344;
					$this->distance = round($this->distance, 2) . ' mi';
				}
				else if ($_GET['distance'] == 'km')
				{
					$this->distance = round($this->distance, 2) .' km';
				}
			}

			return $this->ipAddress . ' - ' . $this->isp . ' (' . $this->distance . ')';
		}

		return $this->ipAddress;
	}

	/**
     * Set's the client's IP from the $_SERVER global into $this->$ip.
     */
	private function setIPAddress()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$this->ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['X-Real-IP']))
		{
			$this->ipAddress = $_SERVER['X-Real-IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$this->ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$this->ipAddress = $_SERVER['REMOTE_ADDR'];
		}

		$this->ipAddress = preg_replace('/^::ffff:/', '', $this->ipAddress);
	}

	/**
     * Uses CURL to call ipinfo.io and get ISP details.
     */
	private function setISPDetails()
	{
		$curl = curl_init($this->_config->speedtest->ipInfoUrl . $this->ipAddress . '/json?token=' . $this->_config->speedtest->ipInfoToken);
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 0,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false
		]);
		$details = json_decode(curl_exec($curl));
		curl_close($curl);

		$this->isp .= isset($details->org) ? $details->org : 'Unknown ISP';
		$this->isp .= isset($details->country) ? ', ' . $details->country : '';
		$this->clientLocaction = isset($details->loc) ? explode(',', $details->loc) : false;

		$curl = curl_init($this->_config->speedtest->ipInfoUrl . 'json?token=' . $this->_config->speedtest->ipInfoToken);
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 0
		]);
		$details = json_decode(curl_exec($curl));
		curl_close($curl);

		$this->serverLocation = isset($details->loc) ? explode(',', $details->loc) : false;
	}

	/**
     * Retrieves the distance to the ISP.
     *
     * @param string $latitudeFrom       Client's latitude
     * @param string $longitudeFrom      Client's longitude
     * @param string $latitudeTo         ISP latitude
     * @param string $longitudeTo        ISP longitude
     */
	private function distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
	{
		$rad = M_PI / 180;
		$theta = $longitudeFrom - $longitudeTo;
		$dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) +  cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);
		$this->distance = acos($dist) / $rad * 60 * 1.853;
	}
}
