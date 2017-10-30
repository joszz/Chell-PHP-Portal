<?php

namespace Chell\Models;

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
    public static function getData($config)
    {
        $curl = curl_init($config->phpsysinfo->URL . "xml.php?json&plugin=complete&t=" . time());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, $config->phpsysinfo->username . ":" . $config->phpsysinfo->password);
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
     *
     * @param object $data       The PHPSysInfo retrieved data, as JSON object. Passed by reference to adjust the data for display.
     */
    private static function setMountClasses(&$data)
    {
        $count = count($data->FileSystem->Mount);

        for($i = 0; $i < $count; $i++)
        {
            $mount = current($data->FileSystem->Mount[$i]);

            if(strpos($mount->MountPoint, 'snap') !== false){
                unset($data->FileSystem->Mount[$i]);
                continue;
            }

            $mount->Class = 'default';

            if($mount->Percent > 90) $mount->Class = 'danger';
            else if($mount->Percent > 70) $mount->Class = 'warning';
            else if($mount->Percent > 50) $mount->Class = 'info';
            else $mount->Class = 'success';

            $data->FileSystem->Mount[$i] = $mount;
        }
    }

    /**
     * Loops through all CpuCores in $data. Formatting temps and CPU speeds.
     *
     * @param object $data       The PHPSysInfo retrieved data, as JSON object. Passed by reference to adjust the data for display.
     * @param object $config     The application's config object.
     */
    private static function setCPUData(&$data, $config)
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