<?php

namespace Chell\Models;

use PDO;

/**
 * The model responsible for all actions related to database statistics.
 *
 * @package Models
 * @suppress PHP2414
 */
class DatabaseStats extends BaseModel
{
    /**
     * Retrieves the database statistics as an array, where key is the statistic name, and the value is the value of the statistic.
     *
     * @return string[]     The database statistics as an array.
     */
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