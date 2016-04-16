<?php

use Phalcon\Http\Response;

class SettingsController extends BaseController
{
    public function indexAction()
    {
        $this->view->forms = array(
            'General'   => new SettingsGeneralForm($this->config),
            'Dashboard' => new SettingsDashboardForm($this->config),
            'Devices'   => new SettingsDevicesForm()
        );

        $this->view->formMenu = new SettingsMenuForm();
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

    public function menuAction()
    {
        $form = new SettingsMenuForm($this->config);
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
        die('todo');
    }

    public function menuitem_deleteAction(){
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