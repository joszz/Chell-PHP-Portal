<?php

namespace Chell\Controllers;

use Phalcon\Http\Response;

use Chell\Models\HyperVAdmin;

/**
 * The controller responsible for all HyperVAdmin related actions.
 *
 * @package Controllers
 */
class HyperVAdminController extends BaseController
{
	/**
	 * Shows a view which displays all VMs in a table.
	 */
	public function vmsAction()
	{
        $this->view->setMainView('layouts/empty');
        $this->view->vms = HyperVAdmin::getVMs($this->config);
	}

    /**
     * Sets the state of the VM to on/off.
     * 
     * @param string $vm        The name of the VM to set state for.
     * @param number $state     The state to set the VM to.
     * @return mixed            Redirect back to vmsAction().
     */
    public function vmToggleStateAction($vm, $state)
    {
        HyperVAdmin::toggleVMState($vm, $state, $this->config);

        return (new Response())->redirect('hyper_v_admin/vms');
    }
}