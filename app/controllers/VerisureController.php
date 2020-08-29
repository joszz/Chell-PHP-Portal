<?php

namespace Chell\Controllers;

use Chell\Models\Verisure;

/**
 * The controller responsible for showing all Verisure related actions.
 *
 * @package Controllers
 */
class VerisureController extends BaseController
{
    /**
     * Called by AJAX to refresh the dashboard widget.
     * Returns a JSON encoded string and dies.
     */
    public function indexAction()
    {
        die(Verisure::GetOverview($this->config, true));
    }

    /**
     * Shows all the details of the Verisure installation.
     */
    public function detailsAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->overflow = true;
        $this->view->overview = Verisure::GetOverview($this->config, false);
        $this->view->log = Verisure::GetLog($this->config);
    }

    public function imageAction($device_label, $image_id, $capture_time)
    {
        $filename = Verisure::GetImage($this->config, $device_label, $image_id, $capture_time);
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($filename));
        die(readfile($filename));
    }

    public function captureimageAction($device_label)
    {
        $output = json_encode(Verisure::CaptureImage($this->config, $device_label));

        if(json_last_error() == JSON_ERROR_NONE)
        {
            die($output);
        }

        http_response_code(500);
    }
}