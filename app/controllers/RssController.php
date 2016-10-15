<?php
/**
 * @package Controllers
 */
class RssController extends BaseController
{
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