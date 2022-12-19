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

    /**
     * Called by the widget to retrieve the playing details.
     * Refreshes the access token if the current access token is expired.
     */
    public function indexAction()
    {
        if ($this->settings->sonos->token_expires - time())
        {
            $this->_model->refreshAccessToken();
        }

        $this->response->setJsonContent($this->_model->getPlayingDetails())->send();
    }

    /**
     * Used in the settings to retrieve the households associated with the account.
     */
    public function householdsAction()
    {
        $this->response->setJsonContent($this->_model->getHouseholds())->send();
    }

    /**
     * Used in the settings to retrieve the groups associated with the account and the household_id posted.
     */
    public function groupsAction()
    {
        $this->response->setJsonContent($this->_model->getGroups( $_POST['sonos-household_id']))->send();
    }

    /**
     * Called by the widget to show the album art of the current playing song.
     */
    public function imageAction()
    {
        $url = urldecode($_GET['url']);
        $client = new Client();
        $output = $client->request('GET', $url)->getBody()->getContents();

        $this->response->setContentType('image/jpeg');
        $this->response->setContent($output)->send();
    }
}