<?php

namespace Chell\Controllers;

use Chell\Forms\SettingsGeneralForm;
use Chell\Forms\SettingsDashboardForm;
use Chell\Forms\SettingsDeviceForm;
use Chell\Forms\SettingsMenuItemForm;
use Chell\Forms\SettingsUserForm;

use Chell\Models\Users;
use Chell\Models\Devices;
use Chell\Models\MenuItems;

use Phalcon\Http\Response;
use Phalcon\Validation\Message;

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

        $this->view->users = Users::Find();
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
     * Deletes a $entity with $id if found.
     * Redirects back to index#devices.
     *
     * @param string    $which     The type of the entity to be deleted. Used with call_user_func to get the right object reference.
     * @param int       $id        The ID of the entity you want to delete.
     */
    public function deleteAction($which, $id)
    {
        if(isset($id, $which))
        {
            $entity = call_user_func(array('Chell\Models\\' . $which, 'findFirst'), array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => intval($id))
            ));

            $entity->delete();
        }

        return (new Response())->redirect('settings/index#' . strtolower($which));
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
     * Shows a form to add/edit a menuitem. If $id is set will edit that menuitem, otherwise it will create a new menuitem.
     * 
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

                return (new Response())->redirect('settings/index#menuitems');
            }
        }

        $this->view->item = $item;
    }

    /**
     * Shows a form to add/edit a users. If $id is set will edit that user, otherwise it will create a new user.
     * 
     * @param int       $id     Optional, the user ID to edit.
     * @return mixed            Will forward to settings/index#users when succesfull, or will show the form again when failed.
     */
    public function userAction($id)
    {
        $user = new Users();

        if(isset($id))
        {
            $user  = Users::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => $id),
            ));

            $user->password ='';
        }

        $form = $this->view->form = new SettingsUserForm($user);

        if ($this->request->isPost())
        {
            $form->bind($data = $this->request->getPost(), $user);

            if($form->isValid($data, $user))
            {
                if(!isset($id))
                {
                    $user = new Users($data);
                }

                if(!empty($user->password) && !empty($data['password_again']) && $user->password == $data['password_again'])
                {
                    $user->password = $this->security->hash($user->password);
                    $user->save();
                    return (new Response())->redirect('settings/index#users');
                }
                else
                {
                    $messages = $form->getMessagesFor('password');

                    $messages->appendMessage(new Message('Password fields should match!') );
                }
            }
        }

        $this->view->user = $user;
    }
}