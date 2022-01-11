<?php

namespace Chell\Models;

use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to YouLess.
 *
 * @package Models
 * @suppress PHP2414
 */
class Youless extends BaseModel
{
	/**
	 * Gets the current power usage from YouLess
	 *
     * @return object        The current statss.
	 */
	public function getCurrentStats()
	{
		$client = new Client();
		$response = $client->request('GET', $this->_settings->youless->url . 'a&f=j');
		$content = json_decode($response->getBody());

		return $content;
	}

	/**
	 * Gets a bootstrap text- class to be used to indicate powerusage thresholds.
	 *
	 * @param int $power		The current power usage.
	 * @return string           A bootstrap class indicating which threshold is passed.
	 */
	public function getTextClass(int $power) : string
	{
		$class = 'text-';

		if ($power <= $this->_settings->youless->threshold_primary)
		{
			$class .=  'success';
		}
		if ($power > $this->_settings->youless->threshold_primary && $power <= $this->_settings->youless->threshold_warning)
		{
			$class .=  'primary';
		}
		if ($power > $this->_settings->youless->threshold_warning && $power <= $this->_settings->youless->threshold_danger)
		{
			$class .=  'warning';
		}
		if ($power > $this->_settings->youless->threshold_danger)
		{
			$class .=  'danger';
		}

		return $class;
	}
}