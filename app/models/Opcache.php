<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to Opcache.
 *
 * @package Models
 */
class Opcache extends Model
{
    public array $status;

    /**
     * Retrieves the Opcache status.
     */
    public function initialize()
    {
        $this->status = opcache_get_status();
    }

    /**
     * Builds up a JSON object to be consumed by ChartistJS.
     *
     * @return string A JSON string that contains all the (formatted) Opcache data in a structured object.
     */
    public function getGraphDataSetJson()
    {
        $dataset['memory'] = [
            round($this->status['memory_usage']['used_memory'] / 1048576, 2),
            round($this->status['memory_usage']['free_memory'] / 1048576 , 2),
            round($this->status['memory_usage']['wasted_memory'] / 1048576, 2),
        ];

        $dataset['keys'] = [
            $this->status['opcache_statistics']['num_cached_keys'],
            $this->status['opcache_statistics']['max_cached_keys'] - $this->status['opcache_statistics']['num_cached_keys']
        ];

        $dataset['hits'] = [
            $this->status['opcache_statistics']['misses'],
            $this->status['opcache_statistics']['hits']
        ];

        $dataset['restarts'] = [
            $this->status['opcache_statistics']['oom_restarts'],
            $this->status['opcache_statistics']['manual_restarts'],
            $this->status['opcache_statistics']['hash_restarts'],
        ];

        return json_encode($dataset);
    }

    /**
     * Formats the scripts in opcache status and applies the paging to it.
     *
     * @param int $page           The page to be displayed, defaults to 1.
     * @param int $totalPages     The total amount of pages. Calculated from the data, passed in by reference to be used in the Controller.
     * @param int $itemsPerPage   The amount of items to show per page.
     * @return array              A paginated array of scripts in Opcache.
     */
    public function getScriptStatusRows(int $page = 1, int &$totalPages, int $itemsPerPage) : array
    {
        $dirs = [];

        foreach ($this->status['scripts'] as $key => $data)
        {
            $dirs[dirname($key)]['files'][basename($key)] = $data;

            if (!isset($dirs[dirname($key)]['memory_usage']))
            {
                $dirs[dirname($key)]['memory_usage'] = 0;
            }
            else
            {
                $dirs[dirname($key)]['memory_usage'] += $data['memory_consumption'];
            }

        }

        asort($dirs);
        $totalPages= ceil(count($dirs) / $itemsPerPage);

        return array_slice($dirs, ($page - 1) * $itemsPerPage, $itemsPerPage, true);
    }

    /**
     * Formats different Opcache data fields.
     *
     * @param string $key    The Opcache field to format.
     * @param string $value  The Opcache data to format.
     * @return string        The formatted data.
     */
    public function getFormattedData(string $key, string $value) : string
    {
        switch($key)
        {
            default:
                return $value;

            case 'used_memory':
            case 'free_memory':
            case 'wasted_memory':
            case 'opcache.memory_consumption':
                if ($value > 1048576)
                {
                    return sprintf('%.2f&nbsp;MB', $value / 1048576);
                }
                else
                {
                    return $value > 1024 ? sprintf('%.2f&nbsp;kB', $value / 1024) : sprintf('%d&nbsp;bytes', $value);
                }

            case 'start_time':
            case 'last_restart_time':
                return $value ? date(DATE_RFC822, $value) : 'Never';

            case 'blacklist_miss_ratio':
            case 'current_wasted_percentage':
            case 'opcache_hit_rate':
            case 'opcache.max_wasted_percentage':
                return number_format($value, 2) . '%';
        }
    }
}
