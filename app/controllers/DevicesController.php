<?php

namespace Chell\Controllers;

use Chell\Models\Devices;
use Phalcon\Http\Response;

/**
 * The controller responsible for handling all actions that have to do with the devices in your network.
 *
 * @package Controllers
 */
class DevicesController extends BaseController
{
    /**
     * This action will try to send a WOL package to the device that is specified $mac.
     *
     * @param int $id   The device Id to WOL.
     */
    public function wolAction($id)
    {
        $device = Devices::findFirst([
           'conditions' => 'id = ?1',
           'bind'       => [1 => $id]
        ]);

        if (isset($device))
        {
            $device->wakeOnLan();
        }

        die;
    }

    /**
     * This action will try to send a shutdown message (RPC) to the device specified by $ip.
     *
     * @param int $id       The device Id to shutdown.
     * @return string       A boolean as string indicating success or failure.
     */
    public function shutdownAction($id)
    {
        $device = Devices::findFirst([
           'conditions' => 'id = ?1',
           'bind'       => [1 => $id]
        ]);

        if (isset($device))
        {
            $output = $device->shutdown();

            if (isset($output[1]))
            {
                die(strpos($output[1], 'succeeded') !== false ? 'true' : 'false');
            }
        }

        die('false');
    }

    /**
     * This action will return the power state of the device identified by IP. Will sent ping to determine state.
     *
     * @param int $id       The device Id to get the on/off state for.
     */
    public function stateAction($id)
    {
        $device = Devices::findFirst([
           'conditions' => 'id = ?1',
           'bind'       => [1 => $id]
        ]);

        $state['state'] = $device->isDeviceOn();
        $state['ip'] = $device->ip;

        $this->view->disable();
        $response = new Response();
        $response->setJsonContent($state)->send();
    }
}
