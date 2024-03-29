<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to Motion.
 *
 * @package Models
 */
class Motion extends BaseModel
{
    public function getLatest() : array
    {
        $files = glob($this->settings->motion->picture_path->value . '*.jpg');
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