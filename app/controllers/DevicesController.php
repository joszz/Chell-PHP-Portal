<?php

namespace Chell\Controllers;

use Chell\Models\Devices;

/**
 * This controller is responsible for handling all actions that have to do with the devices in your network.
 *
 * @package Controllers
 */
class DevicesController extends BaseController
{
    /**
     * This action will try to send a WOL package to the device that is specified by $_GET['mac'].
     *
     * @return  void
     */
    public function wolAction($mac)
    {
        if (isset($mac)){
            Devices::wakeOnLan($mac, $this->config);
        }

        die;
    }

    /**
     * This action will try to send a shutdown message to the device specified by $_GET['ip'].
     *
     * @return  void
     */
    public function shutdownAction($ip, $user, $password)
    {
        if (isset($ip, $user, $password)){
            $output = Devices::shutdown($ip, $user, $password);

            if(isset($output[1]))
            {
                die(strpos($output[1], 'succeeded') !== false ? "true" : "false");
            }
        }

        die("false");
    }

    /**
     * This action will return the power state of each device specified in the database.
     *
     * @return string A JSON encoded object with the state of each device
     */
    public function stateAction($ip)
    {
        $device = Devices::findFirst(array(
           'conditions' => 'ip = ?1',
           'bind'       => array(1 => $ip),
       ));

        $state['state'] = Devices::isDeviceOn($device->ip);
        $state['ip'] = $device->ip;

        die(json_encode($state));
    }

    /**
     * Shows the webtemp image exports for all devices configured.
     *
     * @return View The webtemp view
     */
    public function webtempAction()
    {
        $this->view->setMainView('layouts/empty');

        $this->view->device = Devices::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => intval($_GET['id']),
        )));
    }
}
