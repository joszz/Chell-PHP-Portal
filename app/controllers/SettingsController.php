<?php

namespace Chell\Controllers;

use Chell\Forms\SettingsGeneralForm;
use Chell\Forms\SettingsDashboardForm;
use Chell\Forms\SettingsDeviceForm;
use Chell\Forms\SettingsMenuItemForm;
use Chell\Forms\SettingsUserForm;
use Chell\Forms\SettingsSnmpHostForm;
use Chell\Forms\SettingsSnmpRecordForm;

use Chell\Models\Users;
use Chell\Models\Devices;
use Chell\Models\MenuItems;
use Chell\Models\SnmpHosts;
use Chell\Models\SnmpRecords;

use Davidearl\WebAuthn\WebAuthn;

use Phalcon\Http\Response;
use Phalcon\Messages\Message;

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
     * Initializes the controller, adding JS being used.
     */
    public function initialize()
    {
        parent::initialize();

        if ($this->config->application->debug)
        {
            $this->assets->collection('settings')->addJs('js/settings.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
            $this->assets->collection('settings')->addJs('vendor/webauthn/webauthnregister.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
        }
        else
        {
            $this->assets->collection('settings')->addJs('js/settings.min.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
        }
    }

    /**
     * Shows the settings view
     *
     * @param string Which tab to be active on load, defaults to General.
     */
    public function indexAction($activeTab = 'General')
    {
        $this->view->activeTab = $activeTab;
        $this->view->forms = [
            'General'   => isset($this->generalForm) ? $this->generalForm : new SettingsGeneralForm($this->config),
            'Dashboard' => isset($this->dashboarForm) ? $this-> dashboarForm: new SettingsDashboardForm($this->config),
        ];

        $logsTotal = 0;
        $logs = $this->getLogsOrderedByFilemtime($logsTotal, $this->logsPage);

        $this->view->paginator = self::GetPaginator($this->logsPage, ceil($logsTotal / $this->config->application->itemsPerPage), 'settings/logs/');
        $this->view->users = Users::Find();
        $this->view->devices = Devices::Find();
        $this->view->snmpHosts = SnmpHosts::Find(['order' => 'name']);
        $this->view->menuitems = MenuItems::Find(['order' => 'name']);
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
            if ($form->isValid($data))
            {
                $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
            }
            else
            {
                $this->generalForm = $form;
                return $this->dispatcher->forward([
                    'controller' => 'settings',
                    'action'     => 'index'
                ]);
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
            if ($form->isValid($data))
            {
                $this->writeIniFile($this->config, APP_PATH . 'app/config/config.ini', true);
            }
            else
            {
                $this->dashboarForm = $form;
                return $this->dispatcher->forward([
                    'controller' => 'settings',
                    'action'     => 'index'
                ]);
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
        if (isset($id, $which))
        {
            if ($which == 'Logs')
            {
                if (is_file(APP_PATH .'app/logs/' . $id))
                {
                    unlink(APP_PATH . 'app/logs/' . $id);
                }
                else if ($id == 'all')
                {
                    array_map('unlink', glob(APP_PATH . 'app/logs/*'));
                }
            }
            else
            {
                $entity = call_user_func(['Chell\Models\\' . $which, 'findFirst'], [
                    'conditions' => 'id = ?1',
                    'bind'       => [1 => intval($id)]
                ]);

                $entity->delete();
            }
        }

        return (new Response())->redirect('settings/index#' . strtolower($which));
    }

    /**
     * Shows a form to add/edit a device. If $id is set will edit that device, otherwise it will create a new device.
     *
     * @param int $id                                   Optional, the device to edit.
     * @return void|\Phalcon\Http\ResponseInterface     Will forward to settings/index#devices when successful, or will show the form again when failed.
     */
    public function deviceAction($id = 0)
    {
        $device = new Devices();

        if ($id != 0)
        {
            $device  = Devices::findFirst([
                'conditions' => 'id = ?1',
                'bind'       => [1 => $id],
            ]);
        }

        $form = $this->view->form = new SettingsDeviceForm($device);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $device);

            if ($form->isValid($data, $device))
            {
                if ($id == 0)
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
     * @param int $id                                   Optional, the menuitem to edit.
     * @return void|\Phalcon\Http\ResponseInterface     Will forward to settings/index#menu when successful, or will show the form again when failed.
     */
    public function menuAction($id = 0)
    {
        $item = new MenuItems();

        if ($id != 0)
        {
            $item  = MenuItems::findFirst([
                'conditions' => 'id = ?1',
                'bind'       => [1 => $id],
            ]);
        }

        $form = $this->view->form = new SettingsMenuItemForm($item);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $item);

            if ($form->isValid($data, $item))
            {
                if ($id == 0)
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
     * @param int $id                                   Optional, the user ID to edit.
     * @return void|\Phalcon\Http\ResponseInterface     Will forward to settings/index#users when successful, or will show the form again when failed.
     */
    public function userAction($id = 0)
    {
        $user = new Users();

        if ($id != 0)
        {
            $user  = Users::findFirst([
                'conditions' => 'id = ?1',
                'bind'       => [1 => $id],
            ]);

            $user->password = '';
        }

        $form = $this->view->form = new SettingsUserForm($user);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $user);

            if ($form->isValid($data, $user))
            {
                if ($id == 0)
                {
                    $user = new Users($data);
                }

                if (!empty($user->password) && !empty($data['password_again']) && $user->password == $data['password_again'])
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
     * @param string $file The log filename to display.
     */
    public function logAction($file)
    {
        if (is_file($path = APP_PATH . 'app/logs/' . basename($file)))
        {
            header('Content-Encoding: gzip');
            die(file_get_contents($path));
        }

        die('Log file not found!');
    }

    /**
     * Displays all the log files in a paginated fashion.
     * Calls IndexAction to set all the data for all the tabs displayed.
     *
     * @param int $page The page requested
     */
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
    public function helpAction($id)
    {
        $this->view->setMainView('layouts/empty');
        $this->view->which = $id;
    }

    /**
     * Shows a form to add/edit a SNMP host. If $id is set will edit that host, otherwise it will create a new host.
     *
     * @param int $id                                   Optional, SNMPHost ID to edit.
     * @return void|\Phalcon\Http\ResponseInterface     Will forward to settings/index#snmphosts when successful, or will show the form again when failed.
     */
    public function snmphostAction($id = 0)
    {
        $host = new SnmpHosts();

        if ($id != 0)
        {
            $host  = SnmpHosts::findFirst([
                'conditions' => 'id = ?1',
                'bind'       => [1 => $id],
            ]);
        }

        $form = $this->view->form = new SettingsSnmpHostForm($host);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $host);

            if ($form->isValid($data, $host))
            {
                if ($id == 0)
                {
                    $host = new SnmpHosts($data);
                }

                $host->save();

                return (new Response())->redirect('settings/index#snmphosts');
            }
        }

        $this->view->host = $host;
    }

    /**
     * Shows a form to add/edit a SNMP record. If $id is set will edit that record, otherwise it will create a new record.
     *
     * @param int $id                                   Optional, SNMPRecord ID to edit.
     * @return void|\Phalcon\Http\ResponseInterface     Will forward to settings/snmphost/{id}/#records when successful, or will show the form again when failed.
     */
    public function snmprecordAction($id = 0)
    {
        $record = new SnmpRecords();

        if ($id != 0)
        {
            $record  = SnmpRecords::findFirst([
                'conditions' => 'id = ?1',
                'bind'       => [1 => $id],
            ]);
        }

        $form = $this->view->form = new SettingsSnmpRecordForm($record);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $record);

            if ($form->isValid($data, $record))
            {
                if ($id == 0)
                {
                    $record = new SnmpRecords($data);
                }

                $record->save();

                return (new Response())->redirect('settings/snmphost/' . $record->snmp_host_id . '#records');
            }
        }

        $this->view->record = $record;
    }

    /**
     * Retrieves all log files from the logs directory and sorting them by filemtime descending.
     *
     * @param mixed $totalItems     The total amount of log items passed by reference, calculated form all logs.
     * @param mixed $currentPage    The page to display, defaults to the first page.
     * @return array                The array of logfiles as key and formated datetime as value.
     */
    private function getLogsOrderedByFilemtime(&$totalItems, $currentPage = 1)
    {
        $logs = scandir(APP_PATH . 'app/logs/');
        $logsOrdered = [];

        foreach ($logs as $log)
        {
            if ($log == '.' || $log == '..')
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

    /**
     * Creates a webauthentication registration challenge for the given user.
     * Called through AJAX in settings.js.
     *
     * @param mixed $userId     The Id of the user to create the registration challenge for.
     */
    public function webauthchallengeAction($userId)
    {
        $user  = Users::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $userId],
        ]);

        $webauthn = new WebAuthn($_SERVER['HTTP_HOST']);
        $challenge = $webauthn->prepareChallengeForRegistration($user->username, $user->id, false);
        die(json_encode($challenge));
    }

    /**
     * Creates a new webauthentication and saves it to the database for the posted user Id.
     * Called through AJAX post. Outputs 'success'  or 'failure' indicating to the AJAX call if it succeeded.
     *
     * @todo Add try catch for success/failure messages sent to the browser.
     */
    public function webauthregisterAction()
    {
        if ($this->request->isPost())
        {
            $userId = $this->request->get('userid');
            $registrationData = $this->request->get('registrationdata');

            $user  = Users::findFirst([
                'conditions' => 'id = ?1',
                'bind'       => [1 => $userId],
            ]);

            $webauthn = new WebAuthn($_SERVER['HTTP_HOST']);
            $user->webauthn = $webauthn->register($registrationData, $user->webauthn);
            $user->save();

            die('success');
        }

        die('failed');
    }
}