<?php

namespace Chell\Models;

use DateTime;
use PDO;

/**
 * The model responsible for all actions related to MySQL.
 *
 * @package Models
 */
class DatabaseStats extends BaseModel
{
    public function getStats() : array
    {
        $stats = $this->di->get('db')->getInternalHandler()->getAttribute(PDO::ATTR_SERVER_INFO);
        $stats = explode('  ', $stats);
        $result = [];

        foreach($stats as $stat)
        {
            list($key, $value) = explode(':', $stat);
            $key = str_replace(' ', '_', strtolower($key));
            $result[$key] = $value;
        }

        return $result;
    }
}