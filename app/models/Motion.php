<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class Motion extends Model
{
    /**
     * Main function retrieving PHPSysInfo JSON through cURL.
     *
     * @param object $config    The configuration file to use.
     * @return array            All PHPSysInfo data in an associative array
     */
    public static function getLatest($config)
    {
        $files = glob($config->motion->picturePath . '*.jpg');
        $files = array_combine($files, array_map("filemtime", $files));
        arsort($files);

        return [key($files) => current($files)];
    }

    /**
     * Get's the modified time of the last modified item.
     *
     * @param object $config    The configuration file to use.
     * @return string           A formatted date string.
     */
    public static function getModifiedTime($config)
    {
        $latest_file = self::getLatest($config);
        return date('d-m-Y H:i', current($latest_file));
    }
}