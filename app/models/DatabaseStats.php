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

            if($key == 'uptime')
            {
                $now = new DateTime();
                $uptime = new DateTime();
                $uptime->modify(-$value . ' seconds');
                $value = $now->diff($uptime)->format('%a days, %h hours, %i minutes and %s seconds');
            }

            $result[$key] = $value;
        }

        return $result;
    }
}