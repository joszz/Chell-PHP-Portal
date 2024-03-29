<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Devices;
use Chell\Models\HyperVAdmin;
use Phalcon\Http\ResponseInterface;

/**
 * The controller responsible for all HyperVAdmin related actions.
 *
 * @package Controllers
 */
class HyperVAdminController extends WidgetController
{
	private HyperVAdmin $_model;

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
     *
     * @param int $id	The Id of the device to show the HyperVAdmin VMs and sites for.
	 */
	public function indexAction(int $id)
	{
        $device = $this->view->device = $this->getDevice($id);

		$this->assets->addScripts(['hyperv-admin', 'jquery.isloading']);
		$this->SetEmptyLayout();
		$this->view->vms = $this->_model->getVMs($device);
		$this->view->sites = $this->_model->getSites($device);
	}

	/**
	 * Sets the state of the VM to on/off.
	 *
     * @param int $id				The device Id to do this action for.
	 * @param string $vm			The name of the VM to set state for.
     * @param int $state			The state to set the VM to.
     * @return ResponseInterface	Redirect back to indexAction().
	 */
	public function vmToggleStateAction(int $id, string $vm, int $state) : ResponseInterface
	{
		$this->_model->toggleVMState($this->getDevice($id), $vm, $state);

		return $this->response->redirect('hyper_v_admin/');
	}

	/**
	 * Sets the state of an IIS site to on/off.
	 *
     * @param int $id				The device Id to do this action for.
	 * @param string $site			The name of the site to set state for.
     * @param int $state			The state to set the site to.
     * @return ResponseInterface	Redirect back to indexAction().
	 */
	public function siteToggleStateAction(int $id, string $site, int $state) : ResponseInterface
	{
		$this->_model->toggleSiteState($this->getDevice($id), $site, $state);

		return $this->response->redirect('hyper_v_admin/');
	}

	/**
	 * Used by AJAX functions to retrieve data about the VMs in a JSON string.
     *
     * @param int $id			The device Id to do this action for.
	 */
	public function getVMsAction(int $id)
	{
		$this->view->disable();
		$this->response->setContent($this->_model->getVMs($this->getDevice($id), false))->send();
	}

	/**
	 * Used by AJAX functions to retrieve data about the sites in a JSON string.
     *
     * @param int $id			The device Id to do this action for.
	 */
	public function getSitesAction(int $id)
	{
		$this->view->disable();
		$this->response->setContent($this->_model->getSites($this->getDevice($id), false))->send();
	}

	/**
	 * Retrieves a Device by id, or null when not found.
     *
	 * @param int $id			The Device Id to look for.
     * @return Devices|null		The Device when found, otherwise null.
	 */
	private function getDevice(int $id) : Devices|null
    {
        return Devices::findFirst([
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => [1 => $id],
        ]);
    }
}