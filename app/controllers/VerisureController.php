<?php

namespace Chell\Controllers;

use Chell\Models\Verisure;

/**
 * The controller responsible for all Verisure related actions.
 *
 * @package Controllers
 */
class VerisureController extends BaseController
{
    private $_model;

    /**
     * Initializes the controller, creating a new Verisure model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Verisure();
    }

    /**
     * Called by AJAX to refresh the dashboard widget.
     * Returns a JSON encoded string and dies.
     */
    public function indexAction()
    {
        die($this->_model->getOverview(true));
    }

    /**
     * Shows all the details of the Verisure installation.
     */
    public function detailsAction()
    {
        $this->view->setMainView('layouts/empty');
        $this->view->overflow = true;
        $this->view->overview = $this->_model->getOverview(false);
        $this->view->log = $this->_model->getLog();
        $this->view->firmware = $this->_model->getFirmwareStatus();
    }

    /**
     * Sets the alarm to the requested $state.
     *
     * @param string $state     The state to set the alarm to.
     * @param string $pint      The PIN to use to set the arm state. Only used when not specified in settings
     */
    public function armAction($state, $pin = '')
    {
        $this->_model->setArmState($state, empty($pin) ? $this->config->verisure->securityCode : $pin);
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
        $filename = $this->_model->getImage($device_label, $image_id, $capture_time);
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
        $output = json_encode($this->_model->captureImage($device_label));

        if (json_last_error() == JSON_ERROR_NONE)
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
        die(json_encode($this->_model->getImageSeries()));
    }
}