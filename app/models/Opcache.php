<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 *
 * @package Models
 */
class Opcache extends Model
{
    public $status;

    public function initialize()
    {
        $this->status = opcache_get_status();
    }

    public function getGraphDataSetJson()
    {
        $dataset['memory'] = array(
            round($this->status['memory_usage']['used_memory'] / 1048576, 2),
            round($this->status['memory_usage']['free_memory'] / 1048576 , 2),
            round($this->status['memory_usage']['wasted_memory'] / 1048576, 2),
        );

        $dataset['keys'] = array(
            $this->status['opcache_statistics']['num_cached_keys'],
            $this->status['opcache_statistics']['max_cached_keys'] - $this->status['opcache_statistics']['num_cached_keys']
        );

        $dataset['hits'] = array(
            $this->status['opcache_statistics']['misses'],
            $this->status['opcache_statistics']['hits']
        );

        $dataset['restarts'] = array(
            $this->status['opcache_statistics']['oom_restarts'],
            $this->status['opcache_statistics']['manual_restarts'],
            $this->status['opcache_statistics']['hash_restarts'],
        );

        return json_encode($dataset);
    }

    public function getScriptStatusRows($page = 1, &$totalPages, $itemsPerPage)
    {
        $dirs = array();
        
        foreach ($this->status['scripts'] as $key => $data)
        {
            $dirs[dirname($key)]['files'][basename($key)] = $data;

            if(!isset($dirs[dirname($key)]['memory_usage']))
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

    public function getFormattedData($key, $value)
    {
        switch($key){
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
