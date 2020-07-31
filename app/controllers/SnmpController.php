<?php

namespace Chell\Controllers;

use Phalcon\Mvc\View;

use Chell\Models\SnmpHosts;

class SnmpController extends BaseController
{
    public function hostcontentAction($id)
    {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->host = SnmpHosts::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);
    }

    public function detailsAction($id)
    {
        $this->view->setMainView('layouts/empty');
        $this->view->host = SnmpHosts::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);
    }
}