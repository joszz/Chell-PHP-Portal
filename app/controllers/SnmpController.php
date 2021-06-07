<?php

namespace Chell\Controllers;

use Chell\Models\SnmpHosts;
use Phalcon\Mvc\View;

class SnmpController extends BaseController
{
    /**
     * Shows summarized content of a SNMP host. Used by both the index as well as when updating through AJAX.
     *
     * @param int $id           The Id of the SNMP host.
     * @param string $hidden    Optional, whether to set a Bootstrap hidden class on the main div.
     */
    public function hostcontentAction(int $id, string $hidden = '')
    {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->host = SnmpHosts::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);
        $this->view->hidden = $hidden;

        if ($this->view->host == null)
        {
            die();
        }
    }

    /**
     * Shows all the details of a SNMP host by the given $id.
     *
     * @param int $id The SNMP Host ID.
     */
    public function detailsAction(int $id)
    {
        $this->view->overflow = true;
        $this->view->setMainView('layouts/empty');
        $this->view->host = SnmpHosts::findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);

        if ($this->view->host == null)
        {
            die();
        }
    }
}