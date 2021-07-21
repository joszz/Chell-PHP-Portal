<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Verisure;

/**
 * The controller responsible for all Verisure related actions.
 *
 * @package Controllers
 */
class VerisureController extends WidgetController
{
    private Verisure $_model;

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
        $this->view->disable();
        $this->response->setJsonContent($this->_model->getOverview(false))->send();
    }

    /**
     * Shows all the details of the Verisure installation.
     */
    public function detailsAction()
    {
        $this->SetEmptyLayout();
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
    public function armAction(string $state, string $pin = '')
    {
        $this->_model->setArmState($state, empty($pin) ? $this->settings->verisure->securitycode : $pin);
        $this->view->disable();
        $this->response->setJsonContent('true')->send();
    }

    /**
     * Retrieves the image from the Verisure API, writes it to disk and then output the contents to the browser.
     *
     * @param string $device_label  The device label to retrieve the image for.
     * @param string $image_id      The image Id to retrieve.
     * @param string $capture_time  The capture time, used as the filename.
     */
    public function imageAction(string $device_label, string $image_id, string $capture_time)
    {
        $filename = $this->_model->getImage($device_label, $image_id, $capture_time);

        $this->view->disable();
        $this->response->setContentType('image/jpeg');
        $this->response->setContentLength(filesize($filename));
        $this->response->setContent(readfile($filename))->send();
    }

    /**
     * Calls the Verisure API to capture an image for the requested device (defined by $device_label).
     * If the output of the call is valid JSON, output the $output to the browser. Otherwise set a 500 error.
     *
     * @param string $device_label  The device label to capture an image for.
     */
    public function captureimageAction(string $device_label)
    {
        $this->view->disable();
        $output = $this->_model->captureImage($device_label);
        return $this->response->setJsonContent($output)->send();
    }

    /**
     * Retrieves the imageSeries for the configured account.
     */
    public function imageseriesAction()
    {
        $this->view->disable();
        $this->response->setJsonContent($this->_model->getImageSeries())->send();
    }
}