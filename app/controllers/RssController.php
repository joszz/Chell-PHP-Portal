<?php

namespace Chell\Controllers;

/**
 * The controller responsible for RSS functionality.
 *
 * @package Controllers
 */
class RssController extends BaseController
{
	/**
	* Shows the RSS feed for the dasbboard, containing brief information about the server.
	* Can be used with Pin More to create a live tile in Windows.
	*
	* @see https://www.microsoft.com/nl-nl/store/p/pin-more/9wzdncrdrf2k
	*/
    public function indexAction()
    {
        header('Content-Type: application/xml; charset=UTF-8');

        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off" ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'];
        $url .= $this->config->application->baseUri;

        $this->view->setMainView('layouts/rss_layout');
        $this->view->baseURL = $url;
        $this->view->phpsysinfoData = PHPSysInfo::getData($this->config);
    }
}