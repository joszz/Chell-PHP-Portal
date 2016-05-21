<?php
/**
 * The controller responsible for all dashboard related actions.
 * 
 * @package Chell\Controllers
 */
class IndexController extends BaseController
{
    private $executionTime;

    /**
     * Shows the dashboard view
     * 
     * @return  The dashboard view
     * @todo break up function
     */
    public function indexAction()
    {
        $this->view->menu = Menus::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => 1),
        ));

        $this->view->devices = Devices::find(array('order' => 'name ASC'));
        $this->view->movies = KodiMovies::getLatestMovies();
        $this->view->albums = KodiMusic::getLatestAlbums();
        $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
        
        $this->getPHPSysinfoData();
        $this->view->PHPSysinfoExecutionTime = round(($this->executionTime + microtime(true)) * 1000, 2) . '&micro;s';
    }

    public function getImageAction()
    {
        if(isset($_GET['url']))
        {
            $ntct = Array('1' => 'image/gif',
                          '2' => 'image/jpeg',
                          '3' => 'image/png',
                          '6' => 'image/bmp');
            $filename = getcwd() . '/img/cache/' . basename($_GET['url']);

            if(!file_exists($filename))
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $_GET['url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                file_put_contents($filename, $output);
            }

            header('Content-type: ' . $ntct[exif_imagetype($filename)]);
            die(readfile($filename));
        }
    }

    private function getPHPSysinfoData()
    {
        $this->executionTime = -microtime(true);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->config->dashboard->phpSysInfoURL . "xml.php?json&plugin=complete&t=" . time());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        
        $this->view->phpsysinfoData = json_decode(curl_exec($curl));
        curl_close($curl);

        sort($this->view->phpsysinfoData->Plugins->Plugin_PSStatus->Process);
        usort($this->view->phpsysinfoData->FileSystem->Mount, 
            function($a, $b)
            {
                return strcmp(current($a)->MountPoint, current($b)->MountPoint);
            }
        );

        $this->setPHPSysinfoMountClasses();
        $this->setPHPSysinfoCPUData();
    }

    private function setPHPSysinfoMountClasses()
    {
        for($i = 0; $i < count($this->view->phpsysinfoData->FileSystem->Mount); $i++)
        {
            $mount = current($this->view->phpsysinfoData->FileSystem->Mount[$i]);
            
            if($mount->Percent > 90) $mount->Class = 'danger';
            else if($mount->Percent > 70) $mount->Class = 'warning';
            else if($mount->Percent > 50) $mount->Class = 'info';

            $this->view->phpsysinfoData->FileSystem->Mount[$i] = $mount;
        }
    }

    private function setPHPSysinfoCPUData()
    {
        for($i = 0; $i < count($this->view->phpsysinfoData->Hardware->CPU->CpuCore); $i++)
        {
            $cpuCore = $this->view->phpsysinfoData->Hardware->CPU->CpuCore[$i];

            foreach($this->view->phpsysinfoData->MBInfo->Temperature->Item as $temp) 
            {
                if($temp->{'@attributes'}->Label == 'Core ' . $i)
                {
                    $cpuCore->Temp = $temp->{'@attributes'}->Value . ' &deg;' . $this->view->phpsysinfoData->Options->{'@attributes'}->tempFormat;
                }
            }

            foreach($this->view->phpsysinfoData->MBInfo->Voltage->Item as $voltage)
            {
                if($voltage->{'@attributes'}->Label == $this->config->dashboard->phpSysInfoVCore)
                {
                    $cpuCore->Voltage = $voltage->{'@attributes'}->Value;
                }
            }

            $cpuCore->{'@attributes'}->CpuSpeed = round($cpuCore->{'@attributes'}->CpuSpeed / 1000, 2);
            $cpuCore->{'@attributes'}->CpuSpeedMin = round($cpuCore->{'@attributes'}->CpuSpeedMin / 1000, 2);
            $cpuCore->{'@attributes'}->CpuSpeedMax = round($cpuCore->{'@attributes'}->CpuSpeedMax / 1000, 2);

            $this->view->phpsysinfoData->Hardware->CPU->CpuCore[$i] = $cpuCore;
        }
    }
}