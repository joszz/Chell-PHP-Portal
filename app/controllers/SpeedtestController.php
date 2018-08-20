<?php

namespace Chell\Controllers;

use Chell\Models\Speedtest;

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
}