<?php

namespace Chell\Controllers;

use GuzzleHttp\Client;
use Chell\Controllers\WidgetController;
use Chell\Models\Sonos;

/**
 * The controller responsible for all Verisure related actions.
 *
 * @package Controllers
 */
class SonosController extends WidgetController
{
    private Sonos $_model;

    /**
     * Initializes the controller, creating a new Verisure model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->view->disable();
        $this->_model = new Sonos();
    }

    public function indexAction()
    {
        $this->response->setJsonContent($this->_model->getPlayingDetails())->send();
    }

    public function householdsAction()
    {
        $this->response->setJsonContent($this->_model->getHouseholds())->send();
    }

    public function groupsAction()
    {
        $this->response->setJsonContent($this->_model->getGroups( $_POST['sonos-household_id']))->send();
    }

    public function imageAction()
    {
        $url = urldecode($_GET['url']);
        $client = new Client();
        $output = $client->request('GET', $url)->getBody()->getContents();
        
        $this->response->setContentType('image/jpeg');
        $this->response->setContent($output)->send();
    }
}