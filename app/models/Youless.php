<?php

namespace Chell\Models;

use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to YouLess.
 *
 * @package Models
 */
class Youless extends BaseModel
{
	/**
	 * Gets the current power usage from YouLess
	 *
     * @return object        The current statss.
     * @todo				 Add Basic auth authentication
	 */
	public function getCurrentStats()
	{
		$client = new Client();
		$response = $client->request('GET', $this->settings->youless->url->value . 'a&f=j');
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

		if ($power <= $this->settings->youless->threshold_primary->value)
		{
			$class .=  'success';
		}
		if ($power > $this->settings->youless->threshold_primary->value && $power <= $this->settings->youless->threshold_warning->value)
		{
			$class .=  'primary';
		}
		if ($power > $this->settings->youless->threshold_warning->value && $power <= $this->settings->youless->threshold_danger->value)
		{
			$class .=  'warning';
		}
		if ($power > $this->settings->youless->threshold_danger->value)
		{
			$class .=  'danger';
		}

		return $class;
	}
}