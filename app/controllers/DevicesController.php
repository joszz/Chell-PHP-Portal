<?php
use Phalcon\Mvc\View;

class DevicesController extends BaseController
{
    public function wolAction()
    {
        if (isset($_GET['mac'])){
            Devices::wakeOnLan($_GET['mac']);
        }

        die;
    }

    public function shutdownAction()
    {
        if (isset($_GET['ip']) && isset($_GET['user']) && isset($_GET['password'])){
            echo print_r(Devices::shutdown($_GET['ip'], $_GET['user'], $_GET['password']));
        }

        die;
    }

    public function stateAction()
    {
        $device = Devices::findFirst(array(
           'conditions' => 'ip = ?1',
           'bind'       => array(1 => $_GET['ip']),
       ));

        $state['state'] = Devices::isDeviceOn($device->ip);
        $state['ip'] = $device->ip;

        die(json_encode($state));
    }

    public function webtempAction()
    {
        $this->view->setMainView('layouts/webtemp');

        $this->view->device = Devices::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => intval($_GET['id']),
        )));
    }
}
