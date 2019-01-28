<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to YouLess.
 *
 * @package Models
 */
class Youless extends Model
{

	/**
	 * Gets the current power usage from YouLess
     *
	 * @param mixed $config The config object to get the YouLess URL from.
	 * @return int          The current power usage in watts.
	 */
	public function getCurrentPowerUsage($config)
	{
		$ch = curl_init($config->youless->URL . 'a&f=j');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = json_decode(curl_exec($ch));
		curl_close($ch);

		return $content->pwr;
	}

	/**
	 * Gets a bootstrap text- class to be used to indicate powerusage thresholds.
     * @param mixed $config     The config object to get the different threshold levels for.
	 * @param mixed $power      The current power usage.
	 * @return string           A bootstrap class indicating which threshold is passed.
	 */
	public static function getTextClass($config, $power)
	{
		$class = 'text-';

		if($power <= $config->youless->primaryThreshold)
		{
			$class .=  'success';
		}
		if($power > $config->youless->primaryThreshold && $power <= $config->youless->warnThreshold)
		{
			$class .=  'primary';
		}
		if($power > $config->youless->warnThreshold && $power <= $config->youless->dangerThreshold)
		{
			$class .=  'warn';
		}
		if($power > $config->youless->dangerThreshold)
		{
			$class .=  'danger';
		}

		return $class;
	}
}