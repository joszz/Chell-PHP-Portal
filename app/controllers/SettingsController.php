<?php

use Phalcon\Http\Response;

/**
 * The controller responsible for all setting related actions.
 *
 * @package Controllers
 */
class SettingsController extends BaseController
{

    /**
     * Shows the settings view
     */
    public function indexAction()
    {
        $this->view->activeTab = 'General';
        $this->view->forms = array(
            'General'   => new SettingsGeneralForm($this->config),
            'Dashboard' => new SettingsDashboardForm($this->config),
        );

        //$this->view->formUsers = new SettingsUsersForm(Users::Find());
        $this->view->devices = Devices::Find();
        $this->view->menuitems = MenuItems::Find(array('order' => 'name'));
    }

    /**
     * Handles SettingsGeneralForm post and writes back to config.ini if valid.
     * Forwards to index.
     */
    public function generalAction()
    {
        $this->view->activeTab = 'General';
        $data = $this->request->getPost();
        $form = new SettingsGeneralForm($this->config);

        if($form->isValid($data))
        {
            $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
        }

        return (new Response())->redirect('settings/index#general');
    }

    /**
     * Handles SettingsDashboardForm post and writes back to config.ini if valid.
     * Forwards to index.
     */
    public function dashboardAction()
    {
        $this->view->activeTab = 'Dashboard';
        $data = $this->request->getPost();
        $form = new SettingsDashboardForm($this->config);

        if($form->isValid($data))
        {
            $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
        }

        return (new Response())->redirect('settings/index#dashboard');
    }

    /**
     * Shows a form to add/edit a device. If $id is set will edit that device, otherwise it will create a new device.
     *
     * @param int $id   Optional, the device to edit.
     * @return mixed    Will forward to settings/index#devices when succesfull, or will show the form again when failed.
     */
    public function deviceAction($id)
    {
        $device = new Devices();

        if(isset($id))
        {
            $device  = Devices::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => $id),
            ));
        }


        $form = $this->view->form = new SettingsDeviceForm($device);

        if ($this->request->isPost())
        {
            $form->bind($data = $this->request->getPost(), $device);

            if($form->isValid($data, $device))
            {
                if(!isset($id))
                {
                    $device = new Devices($data);
                }

                $device->save();

                return (new Response())->redirect('settings/index#devices');
            }
        }

        $this->view->device = $device;
    }

    /**
     * Deletes a device if $id is set.
     * Redirects back to index#devices.
     *
     * @param int $id   The ID of the device you want to delete
     */
    public function device_deleteAction($id)
    {
        if(isset($id))
        {
            Devices::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => intval($id))
            ))->delete();
        }

        return (new Response())->redirect('settings/index#devices');
    }

    /**
     * Shows a form to add/edit a menuitem. If $id is set will edit that menuitem, otherwise it will create a new menuitem.
     * @param int $id   Optional, the menuitem to edit.
     * @return mixed    Will forward to settings/index#menu when succesfull, or will show the form again when failed.
     */
    public function menuAction($id)
    {
        $item = new MenuItems();

        if(isset($id))
        {
            $item  = MenuItems::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => $id),
            ));
        }

        $form = $this->view->form = new SettingsMenuItemForm($item);

        if ($this->request->isPost())
        {
            $form->bind($data = $this->request->getPost(), $item);

            if($form->isValid($data, $item))
            {

                if(!isset($id))
                {
                    $item = new MenuItems($data);
                }

                $item->save();

                return (new Response())->redirect('settings/index#menu');
            }
        }

        $this->view->item = $item;
    }

    /**
     * Deletes a menuitem if $id is present.
     * Redirects back to index#menu.
     *
     * @param int $id   The ID od the menuitem to delete
     */
    public function menuitem_deleteAction($id)
    {
        if(isset($id))
        {
            MenuItems::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => intval($id))
            ))->delete();
        }

        return (new Response())->redirect('settings/index#menu');
    }
}