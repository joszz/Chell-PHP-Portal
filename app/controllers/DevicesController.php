<?php

namespace Chell\Controllers;

use Chell\Models\Devices;

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
     * @param string $mac   The MAC address to use to send the WOL packet.
     */
    public function wolAction($mac)
    {
        if (isset($mac))
        {
            Devices::wakeOnLan($mac, $this->config);
        }

        die;
    }

    /**
     * This action will try to send a shutdown message (RPC) to the device specified by $ip.
     *
     * @param string $ip         The IP address to shutdown.
     * @param string $user       The user (with admin credentials) to use to send the RPC command.
     * @param string $password   The password to use to send the RPC command.
     * @return string            A boolean as string indicating success or failure.
     */
    public function shutdownAction($ip, $user, $password)
    {
        if (isset($ip, $user, $password))
        {
            $output = Devices::shutdown($ip, $user, $password);

            if(isset($output[1]))
            {
                die(strpos($output[1], 'succeeded') !== false ? 'true' : 'false');
            }
        }

        die('false');
    }

    /**
     * This action will return the power state of the device identified by IP. Will sent ping to determine state.
     *
     * @param string $ip     The IP address to get the on/off state for.
     * @return string       A JSON encoded object with the state of the device.
     */
    public function stateAction($ip)
    {
        $device = Devices::findFirst([
           'conditions' => 'ip = ?1',
           'bind'       => [1 => $ip]
       ]);

        $state['state'] = Devices::isDeviceOn($device->ip);
        $state['ip'] = $device->ip;

        die(json_encode($state));
    }
}
