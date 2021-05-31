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
     * @return array            All PHPSysInfo data in an associative array
     */
    public function getLatest() : array
    {
        $files = glob($this->_settings->motion->picture_path . '*.jpg');
        $files = array_combine($files, array_map("filemtime", $files));
        arsort($files);

        return [key($files) => current($files)];
    }

    /**
     * Get's the modified time of the last modified item.
     *
     * @return string           A formatted date string.
     */
    public function getModifiedTime() : string
    {
        $latest_file = $this->getLatest();
        return date('d-m-Y H:i', current($latest_file));
    }
}