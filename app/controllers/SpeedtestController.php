<?php

namespace Chell\Controllers;

use Chell\Models\Speedtest;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

/**
 * The controller responsible for all Speedtest related actions.
 *
 * @package Controllers
 */
class SpeedtestController extends BaseController
{
    /**
     * Called by the JavaScript to get the ISP IP and other info.
     */
    public function getIPAction()
    {
        header('Content-Type: text/plain; charset=utf-8');
        die(Speedtest::getIPAddress());
    }

    /**
     * Used for ping and upload tests.
     */
    public function emptyAction()
    {
        header( "HTTP/1.1 200 OK" );
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Connection: keep-alive");

        die();
    }

    /**
     * Used for download tests.
     */
    public function garbageAction()
    {
        ini_set('zlib.output_compression', 'Off');
        ini_set('output_buffering', 'Off');
        ini_set('output_handler', '');

        header('HTTP/1.1 200 OK');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=random.dat');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Generate data
        $data = openssl_random_pseudo_bytes(1048576);

        // Deliver chunks of 1048576 bytes
        $chunks = !empty($_GET['ckSize']) ? intval($_GET['ckSize']) : 4;
        $chunks = $chunks > 100 ? 100 : $chunks;

        for($i = 0; $i < $chunks; $i++)
        {
            echo $data;
            flush();
        }
    }

    /**
     * Called by Speedtest JavaScript to store the telemetry of the current run. Called at the end of the speedtest run.
     */
    public function telemetryAction()
    {
        if ($this->request->isPost())
        {
            $item = new Speedtest($this->request->getPost());
            $item->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            $item->ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $item->lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

            die(var_dump($item->save()));
        }
    }

    /**
     * Used to display the telemetry gathered in a fancybox. Showing both a table and a chartist chart.
     */
    public function statsAction($requestedPage = 1)
    {
        $this->view->setMainView('layouts/empty');

        $paginator = new PaginatorModel([
            'data' => Speedtest::find(array('order' => 'timestamp DESC')),
            'limit' => 10,
            'page' => $requestedPage
        ]);
        $page = self::SetPaginatorEndAndStart($paginator->getPaginate());

        $labels = array();
        $dl = array();
        $ul = array();
        $ping = array();
        $jitter = array();

        foreach($page->items as $stat) {
            $labels[] = $stat->id;

            $dl[] = empty($stat->dl) ? '0' : $stat->dl;
            $ul[] = empty($stat->ul) ? '0' : $stat->ul;
            $ping[] = empty($stat->ping) ? '0' : $stat->ping;
            $jitter[] = empty($stat->jitter) ? '0' : $stat->jitter;
        }

        $this->view->activetab = isset($_GET['activetab']) ? $_GET['activetab'] : 'records';
        $this->view->stats = $page;
        $this->view->labels = array_reverse($labels);
        $this->view->dl = array_reverse($dl);
        $this->view->ul = array_reverse($ul);
        $this->view->ping = array_reverse($ping);
        $this->view->jitter = array_reverse($jitter);
    }
}