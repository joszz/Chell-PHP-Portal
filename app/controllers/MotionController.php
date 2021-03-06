<?php

namespace Chell\Controllers;

use Chell\Models\Motion;

/**
 * The controller responsible for all Motion related actions.
 *
 * @package Controllers
 */
class MotionController extends BaseController
{
    /**
     * Outputs the latest Motion image, based on filemtime.
     */
    public function indexAction()
    {
        $latest_file = Motion::getLatest($this->config);
        $file = key($latest_file);
        $ntct = ['1' => 'image/gif',
                 '2' => 'image/jpeg',
                 '3' => 'image/png',
                 '6' => 'image/bmp'];

        if (is_file($file))
        {
            $resizedPath = getcwd() . '/img/cache/resized/motion/';

            if (!file_exists($resizedPath))
            {
                mkdir($resizedPath);
            }

            $resizedPath .= basename($file);

            $this->resizeImage($file, $resizedPath, 800, 377);

            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header('Content-type: ' . $ntct[exif_imagetype($file)]);

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
            {
                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            }

            echo readfile($resizedPath);
        }

        die;
    }

    /**
     * Gets the modified time for the newest image.
     */
    public function modifiedTimeAction()
    {
        die(Motion::getModifiedTime($this->config));
    }
}