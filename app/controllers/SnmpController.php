<?php

namespace Chell\Controllers;

use Chell\Models\SnmpHosts;

class SnmpController extends BaseController
{
    public function detailsAction($id) {
        $host = SnmpHosts::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => $id),
        ));

        $values = SnmpHosts::setValues($host);

        die(var_dump($values));
    }

}