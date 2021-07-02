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
use Phalcon\Http\ResponseInterface;

/**
 * The controller responsible for all setting related actions.
 *
 * @package Controllers
 */
class SettingsController extends BaseController
{
    private ?SettingsGeneralForm $generalForm = null;
    private ?SettingsDashboardForm $dashboardForm = null;
    private int $logsPage = 1;

    /**
     * Initializes the controller, adding JS being used.
     */
    public function initialize()
    {
        parent::initialize();

        $this->assets->
            addStylesAndScripts(['bootstrap-select', 'bootstrap-toggle', 'jquery.bootstrap-touchspin', 'settings'])->
            addScripts(['webauthnregister', 'bootstrap-tabcollapse', 'toggle-passwords']);
    }

    /**
     * Shows the settings view
     *
     * @param string $activeTab     Which tab to be active on load, defaults to General.
     */
    public function indexAction(string $activeTab = 'General')
    {
        $this->view->activeTab = $activeTab;
        $this->view->forms = [
            'General'   => $this->generalForm ?? new SettingsGeneralForm(),
            'Dashboard' => $this->dashboardForm ?? new SettingsDashboardForm(),
        ];

        $logsTotal = 0;
        $logs = $this->getLogsOrderedByFilemtime($logsTotal, $this->logsPage);

        $this->setScrollToInputErrorElement([$this->generalForm, $this->dashboardForm]);

        $this->view->paginator = self::GetPaginator($this->logsPage, ceil($logsTotal / $this->settings->application->items_per_page), 'settings/logs/');
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
     * @return void|\Phalcon\Http\ResponseInterface     Will forward to settings/index#general when successful, or forwards to indexAction when failed.
     */
    public function generalAction()
    {
        $this->view->activeTab = 'General';
        $data = $this->request->getPost();
        $form = new SettingsGeneralForm();

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->customBind($data);

            if ($form->isValid($data))
            {
                $this->settings->save('general');
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

        return $this->response->redirect('settings/index#general');
    }

    /**
     * Handles SettingsDashboardForm post and writes back to config.ini if valid.
     * Forwards to index.
     *
     * @return void|\Phalcon\Http\ResponseInterface     Will forward to settings/index#dashboard when successful, or forwards to indexAction when failed.
     */
    public function dashboardAction()
    {
        $this->view->activeTab = 'Dashboard';
        $data = $this->request->getPost();
        $form = new SettingsDashboardForm();

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->customBind($data);

            if ($form->isValid($data))
            {
                $this->settings->save('dashboard');
            }
            else
            {
                $this->dashboardForm = $form;
                return $this->dispatcher->forward([
                    'controller' => 'settings',
                    'action'     => 'index'
                ]);
            }
        }

        return $this->response->redirect('settings/index#dashboard');
    }

    /**
     * Deletes a $entity with $id if found. Or deletes a log if $which == 'Log'
     * Redirects back to index#devices.
     *
     * @param string    $which      The type of the entity to be deleted. Used with call_user_func to get the right object reference.
     * @param string       $id      The ID of the entity you want to delete.
     * @param string    $subItem    Used to redirect to the correct page when having nested settings, such as SNMPHost -> SNMPRecord. Defaults to index (top most settings page).
     * @param int|bool  $subItemId  The ID of the subentity you want to delete. Used to redirect to correct parent page.
     * @return ResponseInterface    Will forward to settings/index#$which when successful, or will show the form again when failed.
     */
    public function deleteAction(string $which, string $id, string $subItem = 'index', $subItemId = false) : ResponseInterface
    {
        if (isset($id, $which))
        {
            if ($which == 'Logs')
            {
                if (is_file(APP_PATH . 'app/logs/' . $id))
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

                if($which == 'MenuItems')
                {
                    unlink($entity->getIconFilePath());
                }

                $entity->delete();
            }
        }

        return $this->response->redirect('settings/' . $subItem . ($subItemId ? '/' . $subItemId : null) . '#' . strtolower($which));
    }

    /**
     * Shows a form to add/edit a device. If $id is set will edit that device, otherwise it will create a new device.
     *
     * @param int $id                     Optional, the device to edit.
     * @return void|ResponseInterface     Will forward to settings/index#devices when successful, or will show the form again when failed.
     */
    public function deviceAction(int $id = 0)
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

                return $this->response->redirect('settings/index#devices');
            }
        }

        $this->view->device = $device;
    }

    /**
     * Shows a form to add/edit a menuitem. If $id is set will edit that menuitem, otherwise it will create a new menuitem.
     *
     * @param int $id                     Optional, the menuitem to edit.
     * @return void|ResponseInterface     Will forward to settings/index#menu when successful, or will show the form again when failed.
     */
    public function menuAction(int $id = 0)
    {
        $item = new MenuItems();
        $file = false;

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

                if ($this->request->hasFiles())
                {
                    $file = current($this->request->getUploadedFiles());
                    $item->extension = $file->getExtension();
                }

                $item->save();
                $item->handlePost($data['user_id'] ?? [-1]);

                if ($file)
                {
                    $filename = $item->getIconFilePath();
                    $file->moveTo($filename);
                    $item->resizeIcon($filename);
                }

                return $this->response->redirect('settings/index#menuitems');
            }
        }

        $this->view->item = $item;
    }

    /**
     * Shows a form to add/edit a users. If $id is set will edit that user, otherwise it will create a new user.
     *
     * @param int $id                     Optional, the user ID to edit.
     * @return void|ResponseInterface     Will forward to settings/index#users when successful, or will show the form again when failed.
     */
    public function userAction(int $id = 0)
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

                $user->password = $this->security->hash($user->password);
                $user->save();
                return $this->response->redirect('settings/index#users');
            }
        }

        $this->view->user = $user;
    }

    /**
     * Displays the requested log file.
     *
     * @param string $file The log filename to display.
     */
    public function logAction(string $file)
    {
        if (is_file($path = APP_PATH . 'app/logs/' . basename($file)))
        {
            header('Content-Encoding: gzip');
            $this->response->setHeader('Content-Encoding', 'gzip');
            return $this->response->setContent(file_get_contents($path))->send();
        }

        $this->response->setContent('Log file not found!')->send();
    }

    /**
     * Displays all the log files in a paginated fashion.
     * Calls IndexAction to set all the data for all the tabs displayed.
     *
     * @param int $page The page requested
     */
    public function logsAction(int $page)
    {
        $this->logsPage = $page;
        $this->view->pick('settings/index');
        $this->indexAction('Logs');
    }

    /**
     * Shows help content for an input in settings.
     *
     * @param string $id     The input name to show the help for.
     */
    public function helpAction(string $id)
    {
        $this->view->setMainView('layouts/empty');
        $this->view->which = $id;
    }

    /**
     * Shows a form to add/edit a SNMP host. If $id is set will edit that host, otherwise it will create a new host.
     *
     * @param int $id                     Optional, SNMPHost ID to edit.
     * @return void|ResponseInterface     Will forward to settings/index#snmphosts when successful, or will show the form again when failed.
     */
    public function snmphostAction(int $id = 0)
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

                return $this->response->redirect('settings/index#snmphosts');
            }
        }

        $this->view->host = $host;
    }

    /**
     * Shows a form to add/edit a SNMP record. If $id is set will edit that record, otherwise it will create a new record.
     *
     * @param int $hostId                 The SNMPHost to add/edit a SNMPRecord for.
     * @param int $id                     Optional, SNMPRecord ID to edit.
     * @return void|ResponseInterface     Will forward to settings/snmphost/{id}/#records when successful, or will show the form again when failed.
     */
    public function snmprecordAction(int $hostId, int $id = 0)
    {
        $record = new SnmpRecords();

        if ($id != 0)
        {
            $record  = SnmpRecords::findFirst([
                'conditions' => 'id = ?1',
                'bind'       => [1 => $id],
            ]);
        }
        else {
            $record->snmp_host_id = $hostId;
        }

        $form = $this->view->form = new SettingsSnmpRecordForm($record);

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $form->bind($data = $this->request->getPost(), $record);

            if ($form->isValid())
            {
                if ($id == 0)
                {
                    $record = new SnmpRecords($data);
                }

                $record->save();

                return $this->response->redirect('settings/snmphost/' . $record->snmp_host_id . '#snmprecords');
            }
        }

        $this->view->record = $record;
    }

    /**
     * Retrieves all log files from the logs directory and sorting them by filemtime descending.
     *
     * @param int $totalItems     The total amount of log items passed by reference, calculated form all logs.
     * @param int $currentPage    The page to display, defaults to the first page.
     * @return array              The array of logfiles as key and formated datetime as value.
     */
    private function getLogsOrderedByFilemtime(int &$totalItems, int $currentPage = 1) : array
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
        $logsOrdered = array_slice(array_reverse($logsOrdered), ($currentPage - 1) * $this->settings->application->items_per_page, $this->settings->application->items_per_page);

        return $logsOrdered;
    }

    /**
     * Creates a webauthentication registration challenge for the given user.
     * Called through AJAX in settings.js.
     *
     * @param int $userId     The Id of the user to create the registration challenge for.
     */
    public function webauthchallengeAction(int $userId)
    {
        $user  = Users::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $userId],
        ]);

        $webauthn = new WebAuthn($_SERVER['HTTP_HOST']);
        $challenge = $webauthn->prepareChallengeForRegistration($user->username, $user->id, false);

        $this->view->disable();
        $this->response->setJsonContent($challenge)->send();
    }

    /**
     * Creates a new webauthentication and saves it to the database for the posted user Id.
     * Called through AJAX post. Outputs 'success'  or 'failure' indicating to the AJAX call if it succeeded.
     *
     * @todo Add try catch for success/failure messages sent to the browser.
     */
    public function webauthregisterAction()
    {
        $this->view->disable();

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

            return $this->response->setJsonContent('success')->send();
        }

        $this->response->setJsonContent('failed')->send();
    }

    /**
     * Loops through the forms and returns the first input that contains an error message.
     *
     * @param array $forms      The array of forms to loop through.
     */
    private function setScrollToInputErrorElement(array $forms)
    {
        $this->view->scrollto = '';

        foreach ($forms as $form)
        {
            if ($form)
            {
                $this->view->scrollto = $form->getMessages()[0]->getField();
                break;
            }
        }
    }
}