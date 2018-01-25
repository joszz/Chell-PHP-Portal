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
     * @return array    All PHPSysInfo data in an associative array
     */
    public static function getLatest($config)
    {
        $files = glob($config->motion->picturePath . '*.jpg');
        $files = array_combine($files, array_map("filemtime", $files));
        arsort($files);

        return array(key($files) => current($files));
    }
}