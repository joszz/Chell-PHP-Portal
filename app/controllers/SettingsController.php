<?php

use Phalcon\Http\Response;

class SettingsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        if(!isset($this->view->forms))
        {
            $this->view->activeForm = 'General';

            $this->view->forms = array(
                'General'   => new SettingsGeneralForm($this->config),
                'Dashboard' => new SettingsDashboardForm($this->config),
            );
            
            //$this->view->formUsers = new SettingsUsersForm(Users::Find());
            $this->view->formDevices = new SettingsDevicesForm(Devices::Find());
            $this->view->formMenu = new SettingsMenuItemsForm(MenuItems::Find(array('order' => 'name')));
            $this->view->formNewMenuItem = new SettingsMenuItemsNewForm();
            $this->view->formNewDevice = new SettingsDevicesNewForm();
        }
    }

    public function indexAction()
    {
        
    }

    public function generalAction()
    {
        $data = $this->request->getPost();
        
        if($this->view->forms['General']->isValid($data))
        {
            $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
        }

        return $this->dispatcher->forward(array('action' => 'index'));
    }

    public function dashboardAction()
    {
        $this->view->activeForm = 'Dashboard';
        $data = $this->request->getPost();
        
        if($this->view->forms['Dashboard']->isValid($data))
        {
            $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
        }

        return $this->dispatcher->forward(array('action' => 'index'));
    }

    public function devicesAction()
    {
        $this->view->activeForm = 'Devices';
        $data = $this->request->getPost();
        
        if($devices = $this->view->formDevices->isValid($data))
        {
            foreach($devices as $device)
            {
                if(count($device->getMessages()) == 0)
                {
                    $device->save();
                }
            }
        }
        
        $this->view->formDevices = new SettingsDevicesForm();
        $this->view->formDevices->initialize($devices);

        return $this->dispatcher->forward(array('action' => 'index'));
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
        $data = $this->request->getPost();
        
        if($this->view->formMenu->isValid($data))
        {
            foreach($data['menuitem'] as $menuItemId => $menuItem)
            {
                $item = MenuItems::findFirst(array(
                    'conditions' => 'id = ?1',
                    'bind'       => array(1 => intval($menuItemId))
                ));
                
                //Todo check for $item ! null
                $item->name = $menuItem['name'];
                $item->url = $menuItem['url'];
                $item->icon = $menuItem['icon'];
                $item->device_id = $menuItem['device'];

                $item->save();
            }
        }
        else 
        {
            $this->view->activeForm = 'Menu';
        }

        return $this->dispatcher->forward(array('action' => 'index'));
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