<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class Motion extends BaseModel
{
    /**
     * Main function retrieving PHPSysInfo JSON through cURL.
     *
     * @param object $config    The configuration file to use.
     * @return array            All PHPSysInfo data in an associative array
     */
    public function getLatest() : array
    {
        $files = glob($this->_config->motion->picturePath . '*.jpg');
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
    public function getModifiedTime() : string
    {
        $latest_file = $this->getLatest($this->_config);
        return date('d-m-Y H:i', current($latest_file));
    }
}