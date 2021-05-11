<?php

namespace Chell\Controllers;

use Chell\Models\Devices;
use Chell\Models\HyperVAdmin;
use Phalcon\Http\Response;

/**
 * The controller responsible for all HyperVAdmin related actions.
 *
 * @package Controllers
 */
class HyperVAdminController extends BaseController
{
	private $_model;

	/**
	 * Initializes the controller, creating a new HyperVAdmin model.
	 */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new HyperVAdmin();
    }

	/**
	 * Shows a view which displays all VMs in a table.
	 */
	public function indexAction($id)
	{
        $device = $this->view->device = $this->getDevice($id);

		$this->assets->collection('dashboard')->addJs('js/dashboard-blocks/hyperv-admin.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
		$this->view->setMainView('layouts/empty');
		$this->view->vms = $this->_model->getVMs($device);
		$this->view->sites = $this->_model->getSites($device);
	}

	/**
	 * Sets the state of the VM to on/off.
	 *
	 * @param string $vm        The name of the VM to set state for.
	 * @param number $state     The state to set the VM to.
	 * @return mixed            Redirect back to indexAction().
	 */
	public function vmToggleStateAction($id, $vm, $state)
	{
		$this->_model->toggleVMState($this->getDevice($id), $vm, $state);

		return (new Response())->redirect('hyper_v_admin/');
	}

	/**
	 * Sets the state of an IIS site to on/off.
	 *
	 * @param string $site      The name of the site to set state for.
	 * @param number $state     The state to set the site to.
	 * @return mixed            Redirect back to indexAction().
	 */
	public function siteToggleStateAction($id, $site, $state) : \Phalcon\Http\ResponseInterface
	{
		$this->_model->toggleSiteState($this->getDevice($id), $site, $state);

		return (new Response())->redirect('hyper_v_admin/');
	}

	/**
	 * Used by AJAX functions to retrieve data about the VMs in a JSON string.
	 */
	public function getVMsAction($id)
	{
		die($this->_model->getVMs($this->getDevice($id), false));
	}

	/**
	 * Used by AJAX functions to retrieve data about the sites in a JSON string.
	 */
	public function getSitesAction($id)
	{
		die($this->_model->getSites($this->getDevice($id), false));
	}

	private function getDevice($id)
    {
        return Devices::findFirst([
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => [1 => $id],
        ]);
    }
}