<?php

use Phalcon\Http\Response;

class SettingsController extends BaseController
{
    public function indexAction()
    {
        $this->view->forms = array(
            'General'   => new SettingsGeneralForm($this->config),
            'Dashboard' => new SettingsDashboardForm($this->config),
        );

        $this->view->formDevices = new SettingsDevicesForm(Devices::Find());
        $this->view->formMenu = new SettingsMenuItemsForm(MenuItems::Find(array('order' => 'name')));
        $this->view->formNewMenuItem = new SettingsMenuItemsNewForm();
        $this->view->formNewDevice = new SettingsDevicesNewForm();
    }

    public function generalAction()
    {
        $form = new SettingsGeneralForm($this->config);
        $data = $this->request->getPost();
        
        if($form->isValid($data))
        {
            $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
        }

        return (new Response())->redirect('settings/index');
    }

    public function dashboardAction()
    {
        $form = new SettingsDashboardForm($this->config);
        $data = $this->request->getPost();
        
        if($form->isValid($data))
        {
            $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
        }

        return (new Response())->redirect('settings/index#dashboard');
    }

    public function devicesAction()
    {
        $form = new SettingsDevicesForm(Devices::Find());
        $data = $this->request->getPost();
        
        if($form->isValid($data))
        {
            foreach($data['device'] as $deviceId => $device)
            {
                $item = Devices::findFirst(array(
                    'conditions' => 'id = ?1',
                    'bind'       => array(1 => intval($deviceId))
                ));
                
                //Todo check for $item ! null
                $item->name = $device['name'];
                $item->ip = $device['ip'];
                $item->mac = $device['mac'];
                $item->webtemp = $device['webtemp'];
                $item->shutdown_method= $device['shutdown_method'];
                $item->save();
            }
        }

        return (new Response())->redirect('settings/index#devices');
    }

    public function devices_addAction()
    {
        $data = $this->request->getPost();
        $device = new Devices();
        $form = new SettingsDevicesNewForm($device);
        $form->bind($data, $device);

        if($form->isValid())
        {
            $device->save();
        }

        return (new Response())->redirect('settings/index#devices');
    }

    public function devices_deleteAction()
    {
        if(isset($_GET['id']))
        {
            Devices::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => intval($_GET['id']))
            ))->delete();
        }

        return (new Response())->redirect('settings/index#devices');
    }

    public function menuAction()
    {
        $form = new SettingsMenuItemsForm($this->config);
        $data = $this->request->getPost();
        
        if($form->isValid($data))
        {
            foreach($data['menuitem'] as $menuItemId => $menuItem)
            {
                $item = MenuItems::findFirst(array(
                    'conditions' => 'id = ?1',
                    'bind'       => array(1 => intval($menuItemId))
                ));
                
                //Todo check for $item ! null
                $item->url = $menuItem['url'];
                $item->icon = $menuItem['icon'];
                $item->device = $menuItem['device'];
                $item->save();
            }
        }

        return (new Response())->redirect('settings/index#menu');
    }

    public function menuitem_addAction()
    {
        $data = $this->request->getPost();
        $menuItem = new MenuItems();
        $form = new SettingsMenuItemsNewForm($menuItem);
        $form->bind($data, $menuItem);

        if($form->isValid())
        {
            $menuItem->save();
        }

        return (new Response())->redirect('settings/index#menu');
    }

    public function menuitem_deleteAction()
    {
        if(isset($_GET['id']))
        {
            MenuItems::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => intval($_GET['id']))
            ))->delete();
        }

        return (new Response())->redirect('settings/index#menu');
    }
}