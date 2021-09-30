<?php

namespace Chell\Controllers;

use Chell\Controllers\WidgetController;
use Chell\Models\Speedtest;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use GuzzleHttp\Client;

/**
 * The controller responsible for all Speedtest related actions.
 *
 * @package Controllers
 */
class SpeedtestController extends WidgetController
{
    private Speedtest $_model;

    /**
     * Initializes the controller, creating a new Speedtest model.
     */
	public function initialize()
    {
		parent::initialize();

        $this->_model = new Speedtest();
        $this->view->disable();
    }

    /**
     * Called by the JavaScript to get the ISP IP and other info.
     */
    public function getIPAction()
    {
        $this->response->setContentType('text/plain', 'charset=utf-8');
        $this->response->setContent($this->_model->getIPAddress())->send();
    }

    /**
     * Used for ping and upload tests.
     */
    public function emptyAction()
    {
        $this->response->setStatusCode(200);
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, s-maxage=0, post-check=0, pre-check=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Connection', 'keep-alive');
        $this->response->send();
    }

    /**
     * Used for download tests.
     */
    public function garbageAction()
    {
        ini_set('zlib.output_compression', 'Off');
        ini_set('output_buffering', 'Off');
        ini_set('output_handler', '');

        $this->response->setStatusCode(200);
        $this->response->setContentType('application/octet-stream');
        $this->response->setHeader('Content-Description', 'File Transfer');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=random.dat');
        $this->response->setHeader('Content-Transfer-Encoding', 'binary');
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, s-maxage=0, post-check=0, pre-check=0');
        $this->response->setHeader('Pragma', 'no-cache');

        // Generate data
        $data = openssl_random_pseudo_bytes(1048576);

        // Deliver chunks of 1048576 bytes
        $chunks = !empty($_GET['ckSize']) ? intval($_GET['ckSize']) : 4;
        $chunks = $chunks > 1024 ? 1024 : $chunks;

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
            $item->ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $item->ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $item->lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
            $item->extra = $this->whatIsMyBrowser();

            $this->response->setContent(var_dump($item->save()))->send();
        }
    }

    /**
     * Used to display the telemetry gathered in a fancybox. Showing both a table and a chartist chart.
     *
     * @param string $activeTab     Which Bootstrap tab to set as active.
     * @param int $requestedPage    The requested page for the paginator to display, defaults to 1.
     */
    public function statsAction(string $activeTab = 'records', int $requestedPage = 1)
    {
        $this->assets->addScripts(['chartist', 'chartist-plugin-legend', 'speedtest']);
        $this->view->enable();
        $this->SetEmptyLayout();
        $this->view->overflow = true;

        $paginator = new PaginatorModel([
            'model' => $this->_model,
            'data'  => $this->_model->find(),
            'parameters' => ['order' => 'timestamp DESC'],
            'limit' => $this->settings->application->items_per_page,
            'page'  => $requestedPage
        ]);

        $page = $paginator->paginate();
        $page = self::getPaginator($page->current, $page->last, 'speedtest/stats/' . $activeTab . '/', $page);

        $labels = [];
        $dl = [];
        $ul = [];
        $ping = [];
        $jitter = [];

        foreach($page->items as $stat)
        {
            $labels[] = $stat['id'];

            $dl[] = empty($stat['dl']) ? '0' : $stat['dl'];
            $ul[] = empty($stat['ul']) ? '0' : $stat['ul'];
            $ping[] = empty($stat['ping']) ? '0' : $stat['ping'];
            $jitter[] = empty($stat['jitter']) ? '0' : $stat['jitter'];
        }

        $this->view->activetab =  $activeTab;
        $this->view->stats = $this->view->paginator = $page;
        $this->view->labels = array_reverse($labels);
        $this->view->dl = array_reverse($dl);
        $this->view->ul = array_reverse($ul);
        $this->view->ping = array_reverse($ping);
        $this->view->jitter = array_reverse($jitter);
        $this->view->paginator = $page;
    }

    /**
     * Create an image (PNG) for sharing results of the speedrun. Includes all major data; upload/download/ping/jitter/ISP.
     *
     * @param int $id The ID of the Speedtest run.
     */
    public function shareAction(int $id)
    {
        putenv('GDFONTPATH=' . APP_PATH . 'public/fonts/');

        $item = $this->_model->findFirst([
            'conditions' => 'id = ?1',
            'bind'       => [1 => $id],
        ]);

        $ispinfo = json_decode($item->ispinfo, true)['processedString'];
        $dash = strrpos($ispinfo, '-');

        if ($dash !== false)
        {
            $ispinfo = substr($ispinfo, $dash + 2);
            $par = strrpos($ispinfo, '(');

            if ($par !== false)
            {
                $ispinfo = substr($ispinfo,0,$par);
            }
        }
        else
        {
            $ispinfo = '';
        }

        $scale = 1.25;
        $im = imagecreatetruecolor($width = 530 * $scale, $height = 150 * $scale);
        $bgColor = imagecolorallocate($im, 248, 248, 248);

        $font =  'roboto-light';

        $positionXDownload = 68 * $scale;
        $positionXUpload = 200 * $scale;
        $positionXPing = 330 * $scale;
        $positionXJitter = 460 * $scale;
        $positionXISP = 4 * $scale;

        $downloadText = 'Download';
        $uploadText = 'Upload';
        $pingText = 'Ping';
        $jitterText = 'Jitter';
        $mbpsText = 'Mbps';
        $msText = 'ms';
        $watermarkText = 'HTML5 Speedtest';

        $fonts = [
            'size1'     => ['font' => $font, 'size' => 16 * $scale, 'color' => imagecolorallocate($im, 40, 40, 40), 'position-y' => 24 * $scale],
            'size2'     => ['font' => $font, 'size' => 24 * $scale, 'color' => imagecolorallocate($im, 96, 96, 96), 'position-y' => 78 * $scale],
            'size3'     => ['font' => $font, 'size' => 14 * $scale, 'color' => imagecolorallocate($im, 40, 40, 40), 'position-y' => 118 * $scale],
            'size4'     => ['font' => $font, 'size' => 10 * $scale, 'color' => imagecolorallocate($im, 40, 40, 40), 'position-y' => 146 * $scale],
            'watermark' => ['font' => $font, 'size' => 8 * $scale,  'color' => imagecolorallocate($im, 160, 160, 160), 'position-y' => 146 * $scale],
        ];

        $dlBbox = imageftbbox($fonts['size1']['size'], 0, $fonts['size1']['font'], $downloadText);
        $ulBbox = imageftbbox($fonts['size1']['size'], 0, $fonts['size1']['font'], $uploadText);
        $pingBbox = imageftbbox($fonts['size1']['size'], 0, $fonts['size1']['font'], $pingText);
        $jitBbox = imageftbbox($fonts['size1']['size'], 0, $fonts['size1']['font'], $jitterText);

        $dlMeterBbox = imageftbbox($fonts['size2']['size'], 0, $fonts['size2']['font'], $item->dl);
        $ulMeterBbox = imageftbbox($fonts['size2']['size'], 0, $fonts['size2']['font'], $item->ul);
        $pingMeterBbox = imageftbbox($fonts['size2']['size'], 0, $fonts['size2']['font'], $item->ping);
        $jitMeterBbox = imageftbbox($fonts['size2']['size'], 0, $fonts['size2']['font'], $item->jitter);

        $mbpsBbox = imageftbbox($fonts['size3']['size'], 0, $fonts['size3']['font'], $mbpsText);
        $msBbox = imageftbbox($fonts['size3']['size'], 0, $fonts['size3']['font'], $msText);

        $watermarkBbox = imageftbbox($fonts['watermark']['size'], 0, $fonts['watermark']['font'], $watermarkText);
        $watermarkPositionX = $width - $watermarkBbox[4] - 4 * $scale;

        imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);
        imagefttext($im, $fonts['size1']['size'], 0, $positionXDownload - $dlBbox[4] / 2, $fonts['size1']['position-y'], $fonts['size1']['color'], $fonts['size1']['font'], $downloadText);
        imagefttext($im, $fonts['size1']['size'], 0, $positionXUpload - $ulBbox[4] / 2, $fonts['size1']['position-y'], $fonts['size1']['color'], $fonts['size1']['font'], $uploadText);
        imagefttext($im, $fonts['size1']['size'], 0, $positionXPing - $pingBbox[4] / 2, $fonts['size1']['position-y'], $fonts['size1']['color'], $fonts['size1']['font'], $pingText);
        imagefttext($im, $fonts['size1']['size'], 0, $positionXJitter - $jitBbox[4] / 2, $fonts['size1']['position-y'], $fonts['size1']['color'], $fonts['size1']['font'], $jitterText);

        imagefttext($im, $fonts['size2']['size'], 0, $positionXDownload - $dlMeterBbox[4] / 2, $fonts['size2']['position-y'], $fonts['size2']['color'], $fonts['size2']['font'], $item->dl);
        imagefttext($im, $fonts['size2']['size'], 0, $positionXUpload - $ulMeterBbox[4] / 2, $fonts['size2']['position-y'], $fonts['size2']['color'], $fonts['size2']['font'], $item->ul);
        imagefttext($im, $fonts['size2']['size'], 0, $positionXPing - $pingMeterBbox[4] / 2, $fonts['size2']['position-y'], $fonts['size2']['color'], $fonts['size2']['font'], $item->ping);
        imagefttext($im, $fonts['size2']['size'], 0, $positionXJitter - $jitMeterBbox[4] / 2, $fonts['size2']['position-y'], $fonts['size2']['color'], $fonts['size2']['font'], $item->jitter);

        imagefttext($im, $fonts['size3']['size'], 0, $positionXDownload - $mbpsBbox[4] / 2, $fonts['size3']['position-y'], $fonts['size3']['color'], $fonts['size3']['font'], $mbpsText);
        imagefttext($im, $fonts['size3']['size'], 0, $positionXUpload - $mbpsBbox[4] / 2, $fonts['size3']['position-y'], $fonts['size3']['color'], $fonts['size3']['font'], $mbpsText);
        imagefttext($im, $fonts['size3']['size'], 0, $positionXPing - $msBbox[4] / 2, $fonts['size3']['position-y'], $fonts['size3']['color'], $fonts['size3']['font'], $msText);
        imagefttext($im, $fonts['size3']['size'], 0, $positionXJitter - $msBbox[4] / 2, $fonts['size3']['position-y'], $fonts['size3']['color'], $fonts['size3']['font'], $msText);

        imagefttext($im, $fonts['size4']['size'], 0, $positionXISP, $fonts['size4']['position-y'], $fonts['size4']['color'], $fonts['size4']['font'], $ispinfo);
        imagefttext($im, $fonts['watermark']['size'], 0, $watermarkPositionX, $fonts['watermark']['position-y'], $fonts['watermark']['color'], $fonts['watermark']['font'], $watermarkText);

        $this->response->setContentType('image/png');
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * Try to retrieve browser information
     *
     * @param int       $try    The amount of tries done, if more than 5 it will stop trying
     * @return boolean|string   Either 'false' on failure or a JSON string when succesfull.
     */
    private function whatIsMyBrowser(int $try = 1)
    {
        if ($try > 5)
        {
            return 'false';
        }

        $client = new Client(['headers' => ['X-API-KEY' => $this->settings->speedtest->what_is_my_browser_api_key]]);
        $response = $client->request('POST', $this->settings->speedtest->what_is_my_browser_api_url . 'user_agent_parse', [
            'body' => '{"user_agent":"' . $_SERVER['HTTP_USER_AGENT'] . '"}'
        ]);
        $output = $response->getBody();

        $parsed = json_decode($output);
        if ($parsed->result->code == 'success')
        {
            return $output;
        }
        else if ($parsed->result->message_code == 'usage_limit_exceeded')
        {
            return 'false';
        }

        return $this->whatIsMyBrowser(++$try);
    }
}