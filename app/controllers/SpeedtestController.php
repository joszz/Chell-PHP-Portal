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
            $item->extra = $this->whatIsMyBrowser();

            die(var_dump($item->save()));
        }
    }

    /**
     * Used to display the telemetry gathered in a fancybox. Showing both a table and a chartist chart.
     *
     * @param int       $requestedPage The requested page for the paginator to display, defaults to 1.
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

            if ($stat->extra != 'false') {
                $browser = json_decode($stat->extra);
                $stat->browser = strtolower($browser->parse->software_name);
            }
        }

        $this->view->activetab = isset($_GET['activetab']) ? $_GET['activetab'] : 'records';
        $this->view->stats = $page;
        $this->view->labels = array_reverse($labels);
        $this->view->dl = array_reverse($dl);
        $this->view->ul = array_reverse($ul);
        $this->view->ping = array_reverse($ping);
        $this->view->jitter = array_reverse($jitter);
    }

    public function shareAction($id){
        putenv('GDFONTPATH=' . APP_PATH . 'public/fonts/');

        $item = Speedtest::findFirst(array(
            'conditions' => 'id = ?1',
            'bind'       => array(1 => $id),
        ));

        $ispinfo = json_decode($item->ispinfo, true)["processedString"];
        $dash = strrpos($ispinfo,"-");

        if(!($dash === FALSE)) {
            $ispinfo = substr($ispinfo, $dash+2);
            $par = strrpos($ispinfo, "(");
            if(!($par===FALSE)) {
                $ispinfo=substr($ispinfo,0,$par);
            }
        }
        else $ispinfo="";

        $SCALE = 1.25;
        $WIDTH = 530*$SCALE;
        $HEIGHT = 150*$SCALE;
        $im = imagecreatetruecolor($WIDTH, $HEIGHT);
        $BACKGROUND_COLOR = imagecolorallocate($im, 248, 248, 248);

        $FONT_1 = "OpenSans-Semibold";
        $FONT_2 = $FONT_WATERMARK = "OpenSans-Light";
        $FONT_3 = $FONT_4 = "OpenSans-Semibold";
        $FONT_1_SIZE = 16 * $SCALE;
        $FONT_2_SIZE = 24 * $SCALE;
        $FONT_3_SIZE = 14 * $SCALE;
        $FONT_4_SIZE = 10 * $SCALE;

        $FONT_WATERMARK_SIZE=8*$SCALE;
        $TEXT_COLOR_1 = imagecolorallocate($im, 40, 40, 40);
        $TEXT_COLOR_2 = imagecolorallocate($im, 96, 96, 96);
        $TEXT_COLOR_3 = imagecolorallocate($im, 40, 40, 40);
        $TEXT_COLOR_4 = imagecolorallocate($im, 40, 40, 40);
        $TEXT_COLOR_WATERMARK = imagecolorallocate($im, 160, 160, 160);
        $POSITION_Y_1 = 24 * $SCALE;
        $POSITION_Y_2 = 78 * $SCALE;
        $POSITION_Y_3 = 118 * $SCALE;
        $POSITION_Y_4 = 146 * $SCALE;
        $POSITION_Y_WATERMARK = 146 * $SCALE;
        $POSITION_X_DL = 68 * $SCALE;
        $POSITION_X_UL = 200 * $SCALE;
        $POSITION_X_PING = 330 * $SCALE;
        $POSITION_X_JIT = 460 * $SCALE;
        $POSITION_X_ISP = 4 * $SCALE;
        $DL_TEXT = "Download";
        $UL_TEXT = "Upload";
        $PING_TEXT = "Ping";
        $JIT_TEXT = "Jitter";
        $MBPS_TEXT = "Mbps";
        $MS_TEXT = "ms";
        $WATERMARK_TEXT = "HTML5 Speedtest";

        $dlBbox=imageftbbox($FONT_1_SIZE,0,$FONT_1,$DL_TEXT);
        $ulBbox=imageftbbox($FONT_1_SIZE,0,$FONT_1,$UL_TEXT);
        $pingBbox=imageftbbox($FONT_1_SIZE,0,$FONT_1,$PING_TEXT);
        $jitBbox=imageftbbox($FONT_1_SIZE,0,$FONT_1,$JIT_TEXT);
        $dlMeterBbox=imageftbbox($FONT_2_SIZE,0,$FONT_2,$item->dl);
        $ulMeterBbox=imageftbbox($FONT_2_SIZE,0,$FONT_2,$item->ul);
        $pingMeterBbox=imageftbbox($FONT_2_SIZE,0,$FONT_2,$item->ping);
        $jitMeterBbox=imageftbbox($FONT_2_SIZE,0,$FONT_2,$item->jitter);
        $mbpsBbox=imageftbbox($FONT_3_SIZE,0,$FONT_3,$MBPS_TEXT);
        $msBbox=imageftbbox($FONT_3_SIZE,0,$FONT_3,$MS_TEXT);
        $watermarkBbox=imageftbbox($FONT_WATERMARK_SIZE,0,$FONT_WATERMARK,$WATERMARK_TEXT);
        $POSITION_X_WATERMARK=$WIDTH-$watermarkBbox[4]-4*$SCALE;

        imagefilledrectangle($im, 0, 0, $WIDTH, $HEIGHT, $BACKGROUND_COLOR);
        imagefttext($im,$FONT_1_SIZE,0,$POSITION_X_DL-$dlBbox[4]/2,$POSITION_Y_1,$TEXT_COLOR_1,$FONT_1,$DL_TEXT);
        imagefttext($im,$FONT_1_SIZE,0,$POSITION_X_UL-$ulBbox[4]/2,$POSITION_Y_1,$TEXT_COLOR_1,$FONT_1,$UL_TEXT);
        imagefttext($im,$FONT_1_SIZE,0,$POSITION_X_PING-$pingBbox[4]/2,$POSITION_Y_1,$TEXT_COLOR_1,$FONT_1,$PING_TEXT);
        imagefttext($im,$FONT_1_SIZE,0,$POSITION_X_JIT-$jitBbox[4]/2,$POSITION_Y_1,$TEXT_COLOR_1,$FONT_1,$JIT_TEXT);
        imagefttext($im,$FONT_2_SIZE,0,$POSITION_X_DL-$dlMeterBbox[4]/2,$POSITION_Y_2,$TEXT_COLOR_2,$FONT_2,$item->dl);
        imagefttext($im,$FONT_2_SIZE,0,$POSITION_X_UL-$ulMeterBbox[4]/2,$POSITION_Y_2,$TEXT_COLOR_2,$FONT_2,$item->ul);
        imagefttext($im,$FONT_2_SIZE,0,$POSITION_X_PING-$pingMeterBbox[4]/2,$POSITION_Y_2,$TEXT_COLOR_2,$FONT_2,$item->ping);
        imagefttext($im,$FONT_2_SIZE,0,$POSITION_X_JIT-$jitMeterBbox[4]/2,$POSITION_Y_2,$TEXT_COLOR_2,$FONT_2,$item->jitter);
        imagefttext($im,$FONT_3_SIZE,0,$POSITION_X_DL-$mbpsBbox[4]/2,$POSITION_Y_3,$TEXT_COLOR_3,$FONT_3,$MBPS_TEXT);
        imagefttext($im,$FONT_3_SIZE,0,$POSITION_X_UL-$mbpsBbox[4]/2,$POSITION_Y_3,$TEXT_COLOR_3,$FONT_3,$MBPS_TEXT);
        imagefttext($im,$FONT_3_SIZE,0,$POSITION_X_PING-$msBbox[4]/2,$POSITION_Y_3,$TEXT_COLOR_3,$FONT_3,$MS_TEXT);
        imagefttext($im,$FONT_3_SIZE,0,$POSITION_X_JIT-$msBbox[4]/2,$POSITION_Y_3,$TEXT_COLOR_3,$FONT_3,$MS_TEXT);
        imagefttext($im,$FONT_4_SIZE,0,$POSITION_X_ISP,$POSITION_Y_4,$TEXT_COLOR_4,$FONT_4,$ispinfo);
        imagefttext($im,$FONT_WATERMARK_SIZE,0,$POSITION_X_WATERMARK,$POSITION_Y_WATERMARK,$TEXT_COLOR_WATERMARK,$FONT_WATERMARK,$WATERMARK_TEXT);

        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
        die;
    }

    /**
     * Try to retrieve browser information
     *
     * @param int       $try The amount of tries done, if more than 5 it will stop trying
     * @return mixed    Either 'false' on failure or a JSON string when succesfull.
     */
    private function whatIsMyBrowser($try = 1)
    {
        if($try > 5) {
            return 'false';
        }

        $ch = curl_init($this->config->application->whatIsMyBrowserAPIURL . 'user_agent_parse');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['X-API-KEY:' . $this->config->application->whatIsMyBrowserAPIKey],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '{"user_agent":"' . $_SERVER['HTTP_USER_AGENT'] . '"}',
            CURLOPT_TIMEOUT => 10
        ));

        if(($output = curl_exec($ch)) !== false){
            $parsed = json_decode($output);

            if($parsed->result->code == 'success'){
                return $output;
            }
            else if($parsed->result->message_code == 'usage_limit_exceeded') {
                return 'false';
            }
        }

        curl_close($ch);

        return $this->whatIsMyBrowser(++$try);
    }
}