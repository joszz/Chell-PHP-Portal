<?php

namespace Chell\Controllers;

use Phalcon\Mvc\View;

use Chell\Models\SnmpHosts;

class SnmpController extends BaseController
{
    /**
     * Shows summarized content of a SNMP host. Used by both the index as well as when updating through AJAX.
     */
    public function hostcontentAction($id)
    {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->host = SnmpHosts::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);
    }

    /**
     * Shows all the details of a SNMP host by the given $id.
     *
     * @param int $id The SNMP Host ID.
     */
    public function detailsAction($id)
    {
        $this->view->overflow = true;
        $this->view->setMainView('layouts/empty');
        $this->view->host = SnmpHosts::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);
    }
}