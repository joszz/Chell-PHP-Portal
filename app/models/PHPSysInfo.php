<?php

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class PHPSysInfo extends Model
{
    private $data;

    /**
     * Main function retrieving PHPSysInfo JSON through cURL.
     *
     * @return array    All PHPSysInfo data in an associative array
     */
    public function getData($config)
    {
        $curl = curl_init($config->dashboard->phpSysInfoURL . "xml.php?json&plugin=complete&t=" . time());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, $config->dashboard->phpSysInfoUsername . ":" . $config->dashboard->phpSysInfoPassword);
        $data = json_decode(curl_exec($curl));
        curl_close($curl);

        sort($data->Plugins->Plugin_PSStatus->Process);
        usort($data->FileSystem->Mount,
            function($a, $b)
            {
                return strcmp(current($a)->MountPoint, current($b)->MountPoint);
            }
        );

        self::setMountClasses($data);
        self::setCPUData($data, $config);

        return $data;
    }

    /**
     * Loops through all mounts in $data and adds Bootstrap classes to the objects based on used percentage.
     */
    private function setMountClasses(&$data)
    {
        for($i = 0; $i < count($data->FileSystem->Mount); $i++)
        {
            $mount = current($data->FileSystem->Mount[$i]);

            $mount->Class = 'default';

            if($mount->Percent > 90) $mount->Class = 'danger';
            else if($mount->Percent > 70) $mount->Class = 'warning';
            else if($mount->Percent > 50) $mount->Class = 'info';

            $data->FileSystem->Mount[$i] = $mount;
        }
    }

    /**
     * Loops through all CpuCores in $data. Formatting temps, vCore and CPU speeds.
     */
    private function setCPUData(&$data, $config)
    {
        for($i = 0; $i < count($data->Hardware->CPU->CpuCore); $i++)
        {
            $cpuCore = $data->Hardware->CPU->CpuCore[$i];

            foreach($data->MBInfo->Temperature->Item as $temp)
            {
                if($temp->{'@attributes'}->Label == 'Core ' . $i)
                {
                    $cpuCore->Temp = $temp->{'@attributes'}->Value . ' &deg;' . $data->Options->{'@attributes'}->tempFormat;
                }
            }

            $cpuCore->{'@attributes'}->CpuSpeed = round($cpuCore->{'@attributes'}->CpuSpeed / 1000, 2);
            $cpuCore->{'@attributes'}->CpuSpeedMin = round($cpuCore->{'@attributes'}->CpuSpeedMin / 1000, 2);
            $cpuCore->{'@attributes'}->CpuSpeedMax = round($cpuCore->{'@attributes'}->CpuSpeedMax / 1000, 2);

            $data->Hardware->CPU->CpuCore[$i] = $cpuCore;
        }
    }
}