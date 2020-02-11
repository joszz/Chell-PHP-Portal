<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 */
class Speedtest extends Model
{
	private static $ipAddress = '', $isp = '', $clientLocaction, $serverLocation, $distance;
	private static $ipInfoURL = 'https://ipinfo.io/';
	private static $ipInfoToken = 'https://ipinfo.io/';

	private static $config;

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
	 * @return string The ISP IP and name as a concatenated string.
	 */
	public static function getIPAddress($config)
	{
		self::$config = $config;
		self::setIPAddress();

		if (self::$ipAddress == '::1') {
			return self::$ipAddress . ' - localhost IPv6 access';
		}
		if (stripos(self::$ipAddress, 'fe80:') === 0) {
			return self::$ipAddress . ' - link-local IPv6 access';
		}
		if (strpos(self::$ipAddress, '127.') === 0) {
			return self::$ipAddress . ' - localhost IPv4 access';
		}
		if (strpos(self::$ipAddress, '10.') === 0 || strpos(self::$ipAddress, '192.168.') === 0) {
			return self::$ipAddress . ' - private IPv4 access';
		}
		if (preg_match('/^172\.(1[6-9]|2\d|3[01])\./', self::$ipAddress) === 1) {
			return self::$ipAddress . ' - private IPv4 access';
		}
		if (strpos(self::$ipAddress, '169.254.') === 0) {
			return self::$ipAddress . ' - link-local IPv4 access';
		}

		if (isset($_GET['isp']))
		{
			self::setISPDetails();

			if (isset($_GET['distance']) && self::$clientLocaction && self::$serverLocation)
			{
				self::distance(self::$clientLocaction[0], self::$clientLocaction[1], self::$serverLocation[0], self::$serverLocation[1]);

				if ($_GET['distance'] == 'mi')
				{
					self::$distance /= 1.609344;
					self::$distance = round(self::$distance, 2) . ' mi';
				}
				else if ($_GET['distance'] == 'km')
				{
					self::$distance = round(self::$distance, 2) .' km';
				}
			}

			return self::$ipAddress . ' - ' . self::$isp . ' (' . self::$distance . ')';
		}

		return self::$ipAddress;
	}

	/**
	 * Set's the client's IP from the $_SERVER global into self::$ip.
	 */
	private static function setIPAddress()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			self::$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['X-Real-IP']))
		{
			self::$ipAddress = $_SERVER['X-Real-IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			self::$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			self::$ipAddress = $_SERVER['REMOTE_ADDR'];
		}

		self::$ipAddress = preg_replace('/^::ffff:/', '', self::$ipAddress);
	}

	/**
	 * Uses CURL to call ipinfo.io and get ISP details.
	 */
	private static function setISPDetails()
	{
		$curl = curl_init(self::$config->speedtest->ipInfoUrl . self::$ipAddress . '/json?token=' . self::$config->speedtest->ipInfoToken);
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 0, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false));
		$details = json_decode(curl_exec($curl));
		curl_close($curl);
		
		self::$isp .= isset($details->org) ? $details->org : 'Unknown ISP';
		self::$isp .= isset($details->country) ? ', ' . $details->country : '';
		self::$clientLocaction = isset($details->loc) ? explode(',', $details->loc) : false;

		$curl = curl_init(self::$ipInfoURL . 'json?token=' . self::$config->speedtest->ipInfoToken);
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 0));
		$details = json_decode(curl_exec($curl));
		curl_close($curl);

		self::$serverLocation = isset($details->loc) ? explode(',', $details->loc) : false;
	}

	/**
	 * Retrieves the distance to the ISP.
	 *
	 * @param mixed $latitudeFrom       Client's latitude
	 * @param mixed $longitudeFrom      Client's longitude
	 * @param mixed $latitudeTo         ISP latitude
	 * @param mixed $longitudeTo        ISP longitude
	 */
	private static function distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
	{
		$rad = M_PI / 180;
		$theta = $longitudeFrom - $longitudeTo;
		$dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) +  cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);
		self::$distance = acos($dist) / $rad * 60 * 1.853;
	}
}
