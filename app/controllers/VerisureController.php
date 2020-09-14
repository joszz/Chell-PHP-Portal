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
        $this->view->firmware = Verisure::GetFirmwareStatus($this->config);
    }

    /**
     * Sets the alarm to the requested $state.
     *
     * @param string $state     The state to set the alarm to.
     * @param integer $state    The PIN to use to set the arm state. Only used when not specified in settings
     */
    public function armAction($state, $pin)
    {
        Verisure::SetArmState($this->config, $state, empty($pin) ? $this->config->verisure->securityCode : $pin);
        die("true");
    }

    /**
     * Retrieves the image from the Verisure API, writes it to disk and then output the contents to the browser.
     *
     * @param string $device_label  The device label to retrieve the image for.
     * @param string $image_id      The image Id to retrieve.
     * @param string $capture_time  The capture time, used as the filename.
     */
    public function imageAction($device_label, $image_id, $capture_time)
    {
        $filename = Verisure::GetImage($this->config, $device_label, $image_id, $capture_time);
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($filename));
        die(readfile($filename));
    }

    /**
     * Calls the Verisure API to capture an image for the requested device (defined by $device_label).
     * If the output of the call is valid JSON, output the $output to the browser. Otherwise set a 500 error.
     *
     * @param string $device_label  The device label to capture an image for.
     */
    public function captureimageAction($device_label)
    {
        $output = json_encode(Verisure::CaptureImage($this->config, $device_label));

        if(json_last_error() == JSON_ERROR_NONE)
        {
            die($output);
        }

        http_response_code(500);
    }

    /**
     * Retrieves the imageSeries for the configured account.
     */
    public function imageseriesAction()
    {
        die(json_encode(verisure::GetImageSeries($this->config)));
    }
}