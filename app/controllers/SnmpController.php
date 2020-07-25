<?php

namespace Chell\Controllers;

use Chell\Models\SnmpHosts;

class SnmpController extends BaseController
{
    public function detailsAction($id) {
        $host = SnmpHosts::findFirst(array(
            'conditions' => 'id = ?1',
            'bind'       => array(1 => $id),
        ));

        $values = $host->getValues($host);

        die(var_dump($values));
    }

}