<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Devices;

/**
 * The controller responsible for handling all actions that have to do with the devices in your network.
 *
 * @package Controllers
 */
class DevicesController extends WidgetController
{
    /**
     * Initializes the controller, disabling the view for all actions/
     */
    public function initialize()
	{
		parent::initialize();

		$this->view->disable();
	}

    /**
     * This action will try to send a WOL package to the device specified by $id.
     *
     * @param int $id   The device Id to WOL.
     */
    public function wakeAction(int $id)
    {
        $device = Devices::findFirst([
           'conditions' => 'id = ?1',
           'bind'       => [1 => $id]
        ]);

        $this->response->setJsonContent($device ? $device->wake() : false)->send();
    }

    /**
     * This action will try to send a shutdown message (RPC) to the device specified by $id.
     *
     * @param int $id       The device Id to shutdown.
     */
    public function shutdownAction(int $id)
    {
        $device = Devices::findFirst([
           'conditions' => 'id = ?1',
           'bind'       => [1 => $id]
        ]);

        $this->response->setJsonContent($device ? $device->shutdown() : false)->send();
    }

    /**
     * This action will return the power state of the device identified by id. Will sent ping to determine state.
     *
     * @param int $id       The device Id to get the on/off state for.
     */
    public function stateAction(int $id)
    {
        $device = Devices::findFirst([
           'conditions' => 'id = ?1',
           'bind'       => [1 => $id]
        ]);

        $state['state'] = $device->isDeviceOn();
        $state['ip'] = $device->ip;

        $this->response->setJsonContent($state)->send();
    }

    public function detailsAction(int $id)
    {
        $device = Devices::findFirst([
           'conditions' => 'id = ?1',
           'bind'       => [1 => $id]
        ]);
        $this->view->enable();
        $this->view->architecture = $device->adbGetArchitecture();
        $this->view->cpuUsage = $device->adbGetCpuUsage();
    }
}
