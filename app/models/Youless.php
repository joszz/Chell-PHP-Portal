<?php

namespace Chell\Models;

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
	 */
	public function getCurrentStats()
	{
		$ch = curl_init($this->_config->youless->URL . 'a&f=j');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = json_decode(curl_exec($ch));
		curl_close($ch);

		return $content;
	}

	/**
	 * Gets a bootstrap text- class to be used to indicate powerusage thresholds.
	 *
	 * @param mixed $config     The config object to get the different threshold levels for.
	 * @param mixed $power      The current power usage.
	 * @return string           A bootstrap class indicating which threshold is passed.
	 */
	public static function getTextClass($config, $power)
	{
		$class = 'text-';

		if ($power <= $config->youless->primaryThreshold)
		{
			$class .=  'success';
		}
		if ($power > $config->youless->primaryThreshold && $power <= $config->youless->warnThreshold)
		{
			$class .=  'primary';
		}
		if ($power > $config->youless->warnThreshold && $power <= $config->youless->dangerThreshold)
		{
			$class .=  'warning';
		}
		if ($power > $config->youless->dangerThreshold)
		{
			$class .=  'danger';
		}

		return $class;
	}
}