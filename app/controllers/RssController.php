<?php

namespace Chell\Controllers;

use Chell\Models\Disks;
use Chell\Models\Sysinfo;
use Phalcon\Mvc\View;

/**
 * The controller responsible for RSS functionality.
 *
 * @package Controllers
 */
class RssController extends BaseController
{
	/**
	* Shows the RSS feed for the dashboard, containing brief information about the server.
	* Can be used with Pin More to create a live tile in Windows.
	*
	* @see https://www.microsoft.com/nl-nl/store/p/pin-more/9wzdncrdrf2k
	*/
	public function indexAction()
	{
		$this->view->sysinfoData = (new Sysinfo())->getData();
		$this->view->disksData = (new Disks())->getStats();

		$this->response->setContentType('application/xml', 'charset=UTF-8');
		$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
	}
}