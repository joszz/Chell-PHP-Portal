<?php

namespace Chell\Controllers;

use Phalcon\Mvc\View;

use Chell\Models\SnmpHosts;

class SnmpController extends BaseController
{
    public function hostcontentAction($id) {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->host = SnmpHosts::findFirst(array(
            'conditions' => 'id = ?1',
            'bind'       => array(1 => $id),
        ));
    }

    public function detailsAction($id) {
        $host = SnmpHosts::findFirst(array(
            'conditions' => 'id = ?1',
            'bind'       => array(1 => $id),
        ));

        $values = $host->getValues($host);

        die(var_dump($values));
    }
}