<?php

class SettingsController extends BaseController
{
    public function indexAction()
    {
        $this->view->form = new SettingsForm($this->config);
    }
}
