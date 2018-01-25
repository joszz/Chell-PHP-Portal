<?php

namespace Chell\Controllers;

use Chell\Models\Motion;
/**
 *
 *
 * @package Controllers
 */
class MotionController extends BaseController
{
    public function indexAction()
    {
        $latest_file = Motion::getLatest($this->config);
        $file = key($latest_file);
        $filemtime = current($latest_file);
        $ntct = Array('1' => 'image/gif',
                      '2' => 'image/jpeg',
                      '3' => 'image/png',
                      '6' => 'image/bmp');

        if (is_file($file))
        {
            $resizedPath = getcwd() . '/img/cache/resized/motion/';

            if(!file_exists($resizedPath))
            {
                mkdir($resizedPath);
            }

            $resizedPath .= basename($file);

            if(!file_exists($resizedPath))
            {
                $this->resizeImage($file, $resizedPath, 800, 377);
            }

            session_cache_limiter('none');
            header('Cache-control: max-age='.(60 * 60 * 24 * 365));
            header('Expires: '.gmdate(DATE_RFC1123 ,time()+ 60 * 60 * 24 * 365));
            header('Last-Modified: '.gmdate(DATE_RFC1123, $filemtime));
            header('Content-type: ' . $ntct[exif_imagetype($file)]);
            header("Pragma: cache");

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                header('HTTP/1.1 304 Not Modified');
            }

            die(readfile($resizedPath));
        }

        die;
    }
}