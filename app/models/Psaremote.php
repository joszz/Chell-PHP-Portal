<?php

namespace Chell\Models;

/**
 * @package Models
 */
class Psaremote extends BaseModel
{
    public function GetVehicleInfo($cache = true)
    {
        $curl = $this->getCurl('get_vehicleinfo/' . $this->_settings->psa_remote->vin . ($cache ? '?from_cache=1' : null));
        $result = json_decode(curl_exec($curl));
        curl_close($curl);
        $output = $result;

        foreach ($result->energy as $energy)
        {
            if ($energy->type == $result->service->type)
            {
                $output->energy = $energy;
            }
        }

        return $output;
    }

    private function getCurl(string $url)
    {
        $ch = curl_init($this->_settings->psa_remote->url . $url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERPWD => $this->_settings->psa_remote->username . ':' . $this->_settings->psa_remote->password,
            CURLOPT_TIMEOUT => 3
        ]);
        return $ch;
    }
}