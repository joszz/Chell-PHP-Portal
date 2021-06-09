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
    private Motion $_model;

    /**
     * Initializes the controller, creating a new Motion model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Motion();
        $this->view->disable();
    }

    /**
     * Outputs the latest Motion image, based on filemtime.
     */
    public function indexAction()
    {
        $latest_file = $this->_model->getLatest();
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

			$this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, s-maxage=0, post-check=0, pre-check=0');
			$this->response->setContentType($ntct[exif_imagetype($file)]);
			$this->response->setHeader('Pragma', 'no-cache');

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
            {
                $this->response->setNotModified();
            }

            $this->response->setContent(readfile($resizedPath))->send();
        }
    }

    /**
     * Gets the modified time for the newest image.
     */
    public function modifiedTimeAction()
    {
        $this->response->setJsonContent($this->_model->getModifiedTime())->send();
    }
}