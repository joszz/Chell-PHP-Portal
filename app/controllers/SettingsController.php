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
    private $generalForm, $dashboarForm;
    private $logsPage = 1;

    /**
     * Shows the settings view
     */
    public function indexAction($activeTab = 'General')
    {
        $this->view->activeTab = $activeTab;
        $this->view->forms = array(
            'General'   => isset($this->generalForm) ? $this->generalForm : new SettingsGeneralForm($this->config),
            'Dashboard' => isset($this->dashboarForm) ? $this-> dashboarForm: new SettingsDashboardForm($this->config),
        );

        $logsTotal = 0;
        $logs = $this->getLogsOrderedByFilemtime($logsTotal, $this->logsPage);

        $this->view->paginator = self::GetPaginator($this->logsPage, ceil($logsTotal / $this->config->application->itemsPerPage), 'settings/logs/');
        $this->view->users = Users::Find();
        $this->view->devices = Devices::Find();
        $this->view->menuitems = MenuItems::Find(array('order' => 'name'));
        $this->view->logs = $logs;
    }

    /**
     * Handles SettingsGeneralForm post and writes back to config.ini if valid.
     * Forwards to index.
     *
     * @todo Review comments
     */
    public function generalAction()
    {
        $this->view->activeTab = 'General';
        $data = $this->request->getPost();
        $form = new SettingsGeneralForm($this->config);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            if($form->isValid($data))
            {
                $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
            }
            else
            {
                $this->generalForm = $form;
                return $this->dispatcher->forward(
                    array(
                        'controller' => 'settings',
                        'action'     => 'index'
                    )
                );
            }
        }

        return (new Response())->redirect('settings/index#general');
    }

    /**
     * Handles SettingsDashboardForm post and writes back to config.ini if valid.
     * Forwards to index.
     *
     * @todo Review comments
     */
    public function dashboardAction()
    {
        $this->view->activeTab = 'Dashboard';
        $data = $this->request->getPost();
        $form = new SettingsDashboardForm($this->config);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            if($form->isValid($data))
            {
                $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
            }
            else
            {
                $this->dashboarForm = $form;
                return $this->dispatcher->forward(
                    array(
                        'controller' => 'settings',
                        'action'     => 'index'
                    )
                );
            }
        }

        return (new Response())->redirect('settings/index#dashboard');
    }

    /**
     * Deletes a $entity with $id if found. Or deletes a log if $which == 'Log'
     * Redirects back to index#devices.
     *
     * @param string    $which     The type of the entity to be deleted. Used with call_user_func to get the right object reference.
     * @param int       $id        The ID of the entity you want to delete.
     */
    public function deleteAction($which, $id)
    {
        if(isset($id, $which))
        {
            if($which == 'Logs')
            {
                if(is_file(APP_PATH .'app/logs/' . $id))
                {
                    unlink(APP_PATH . 'app/logs/' . $id);
                }
                else if($id == 'all'){
                    array_map('unlink', glob(APP_PATH . 'app/logs/*'));
                }
            }
            else
            {
                $entity = call_user_func(array('Chell\Models\\' . $which, 'findFirst'), array(
                    'conditions' => 'id = ?1',
                    'bind'       => array(1 => intval($id))
                ));

                $entity->delete();
            }
        }

        return (new Response())->redirect('settings/index#' . strtolower($which));
    }

    /**
     * Shows a form to add/edit a device. If $id is set will edit that device, otherwise it will create a new device.
     *
     * @param int $id   Optional, the device to edit.
     * @return mixed    Will forward to settings/index#devices when successful, or will show the form again when failed.
     */
    public function deviceAction($id = 0)
    {
        $device = new Devices();

        if($id != 0)
        {
            $device  = Devices::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => $id),
            ));
        }

        $form = $this->view->form = new SettingsDeviceForm($device);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $device);

            if($form->isValid($data, $device))
            {
                if($id == 0)
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
     * @return mixed    Will forward to settings/index#menu when successful, or will show the form again when failed.
     */
    public function menuAction($id = 0)
    {
        $item = new MenuItems();

        if($id != 0)
        {
            $item  = MenuItems::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => $id),
            ));
        }

        $form = $this->view->form = new SettingsMenuItemForm($item);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $item);

            if($form->isValid($data, $item))
            {
                if($id == 0)
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
     * @return mixed            Will forward to settings/index#users when successful, or will show the form again when failed.
     */
    public function userAction($id = 0)
    {
        $user = new Users();

        if($id != 0)
        {
            $user  = Users::findFirst(array(
                'conditions' => 'id = ?1',
                'bind'       => array(1 => $id),
            ));

            $user->password = '';
        }

        $form = $this->view->form = new SettingsUserForm($user);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $user);

            if($form->isValid($data, $user))
            {
                if($id == 0)
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

    /**
     * Displays the requested log file.
     *
     * @param string $file      The log filename to display.
     */
    public function logAction($file)
    {
        if(is_file($path = APP_PATH . 'app/logs/' . basename($file)))
        {
            header('Content-Encoding: gzip');
            die(file_get_contents($path));
        }

        die('Log file not found!');
    }

    public function logsAction($page)
    {
        $this->logsPage = $page;
        $this->view->pick('settings/index');
        $this->indexAction('Logs');
    }

    /**
     * Shows help content for an input in settings.
     *
     * @param string $which     The input name to show the help for.
     */
    public function helpAction($which)
    {
        $this->view->setMainView('layouts/empty');
        $this->view->which = $which;
    }

    /**
     * Retrieves all log files from the logs directory and sorting them by filemtime descending.
     *
     * @return array    The array of logfiles as key and formated datetime as value.
     */
    private function getLogsOrderedByFilemtime(&$totalItems, $currentPage = 1)
    {
        $logs = scandir(APP_PATH . 'app/logs/');
        $logsOrdered = array();

        foreach ($logs as $log)
        {
            if($log == '.' || $log == '..')
            {
                continue;
            }

            $logsOrdered[$log] = date('d-m-Y H:i:s', filemtime(APP_PATH . 'app/logs/' . $log));
        }

        asort($logsOrdered);
        $totalItems = count($logsOrdered);
        $logsOrdered = array_slice(array_reverse($logsOrdered), ($currentPage - 1) * $this->config->application->itemsPerPage, $this->config->application->itemsPerPage);

        return $logsOrdered;
    }
}