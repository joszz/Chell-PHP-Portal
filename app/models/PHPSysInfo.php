<?php

class PHPSysInfo extends BaseModel
{
    private $data;

    public function getData()
    {
        $curl = curl_init($this->config->dashboard->phpSysInfoURL . "xml.php?json&plugin=complete&t=" . time());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $this->data = json_decode(curl_exec($curl));
        curl_close($curl);

        sort($this->data->Plugins->Plugin_PSStatus->Process);
        usort($this->data->FileSystem->Mount, 
            function($a, $b)
            {
                return strcmp(current($a)->MountPoint, current($b)->MountPoint);
            }
        );

        self::setMountClasses();
        self::setCPUData();

        return $this->data;
    }

    private function setMountClasses()
    {
        for($i = 0; $i < count($this->data->FileSystem->Mount); $i++)
        {
            $mount = current($this->data->FileSystem->Mount[$i]);
            
            if($mount->Percent > 90) $mount->Class = 'danger';
            else if($mount->Percent > 70) $mount->Class = 'warning';
            else if($mount->Percent > 50) $mount->Class = 'info';

            $this->data->FileSystem->Mount[$i] = $mount;
        }
    }

    private function setCPUData()
    {
        for($i = 0; $i < count($this->data->Hardware->CPU->CpuCore); $i++)
        {
            $cpuCore = $this->data->Hardware->CPU->CpuCore[$i];

            foreach($this->data->MBInfo->Temperature->Item as $temp) 
            {
                if($temp->{'@attributes'}->Label == 'Core ' . $i)
                {
                    $cpuCore->Temp = $temp->{'@attributes'}->Value . ' &deg;' . $this->data->Options->{'@attributes'}->tempFormat;
                }
            }

            foreach($this->data->MBInfo->Voltage->Item as $voltage)
            {
                if($voltage->{'@attributes'}->Label == $this->config->dashboard->phpSysInfoVCore)
                {
                    $cpuCore->Voltage = $voltage->{'@attributes'}->Value;
                }
            }

            $cpuCore->{'@attributes'}->CpuSpeed = round($cpuCore->{'@attributes'}->CpuSpeed / 1000, 2);
            $cpuCore->{'@attributes'}->CpuSpeedMin = round($cpuCore->{'@attributes'}->CpuSpeedMin / 1000, 2);
            $cpuCore->{'@attributes'}->CpuSpeedMax = round($cpuCore->{'@attributes'}->CpuSpeedMax / 1000, 2);

            $this->data->Hardware->CPU->CpuCore[$i] = $cpuCore;
        }
    }
}