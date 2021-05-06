<?php

namespace Chell\Controllers;

use Chell\Models\Pulseway;

/**
 * The controller responsible for all Pulseway related actions.
 *
 * @package Controllers
 */
class PulsewayController extends BaseController
{
    private $_model;

    /**
     * Initializes the controller, creating a new Pulseway model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Pulseway();
    }

    /**
     * Contacts the Pulseway API for each specified system in the config.
     * Formats the uptime and sents back the data as a JSON encoded string.
     */
    public function indexAction()
    {
        $systems = explode(',', $this->config->pulseway->systems);
        $result = [];

        foreach ($systems as $system)
        {
            $data = $this->_model->getSystem($system);
            list($days, $hours, $minutes) = explode(', ', $data->uptime);
            $hours = $this->formatTimePart($hours);
            $minutes = $this->formatTimePart($minutes);
            $data->uptime = $days . ' ' . $hours . ':' . $minutes;
            $result[] = $data;
        }

        die(json_encode($result));
    }

    /**
     * Formats a time part (hours, minutes etc) removing the words (seperated by the first space encountered) and only leaving the zero padded values.
     * @param mixed $timePart 
     * @return string
     */
    private function formatTimePart($timePart)
    {
        return str_pad(substr($timePart, 0, strpos($timePart, ' ')), 2, '0', STR_PAD_LEFT);
    }
}