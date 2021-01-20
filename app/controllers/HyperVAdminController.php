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
	public function indexAction()
	{
		$this->assets->collection('dashboard')->addJs('js/dashboard-blocks/hyperv-admin.js', true, false, ['defer' => 'defer'], $this->config->application->version, true);
		$this->view->setMainView('layouts/empty');
		$this->view->vms = HyperVAdmin::getVMs($this->config);
		$this->view->sites = HyperVAdmin::getSites($this->config);
	}

	/**
	 * Sets the state of the VM to on/off.
	 *
	 * @param string $vm        The name of the VM to set state for.
	 * @param number $state     The state to set the VM to.
	 * @return mixed            Redirect back to indexAction().
	 */
	public function vmToggleStateAction($vm, $state)
	{
		HyperVAdmin::toggleVMState($vm, $state, $this->config);

		return (new Response())->redirect('hyper_v_admin/');
	}

	/**
	 * Sets the state of an IIS site to on/off.
	 *
	 * @param string $site      The name of the site to set state for.
	 * @param number $state     The state to set the site to.
	 * @return mixed            Redirect back to indexAction().
	 */
	public function siteToggleStateAction($site, $state)
	{
		HyperVAdmin::toggleSiteState($site, $state, $this->config);

		return (new Response())->redirect('hyper_v_admin/');
	}

	/**
	 * Used by AJAX functions to retrieve data about the VMs in a JSON string.
	 */
	public function getVMsAction()
	{
		die(HyperVAdmin::getVMs($this->config, false));
	}

	/**
	 * Used by AJAX functions to retrieve data about the sites in a JSON string.
	 */
	public function getSitesAction()
	{
		die(HyperVAdmin::getSites($this->config, false));
	}
}