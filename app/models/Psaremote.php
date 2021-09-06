<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to PSA Remote.
 *
 * @package Models
 */
class Psaremote extends BaseModel
{
    public function GetVehicleInfo(bool $cache = true)
    {
        $curl = $this->getCurl('get_vehicleinfo/' . $this->_settings->psaremote->vin . ($cache ? '?from_cache=1' : null));
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
        $ch = curl_init($this->_settings->psaremote->url . $url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERPWD => $this->_settings->psaremote->username . ':' . $this->_settings->psaremote->password,
            CURLOPT_TIMEOUT => 3
        ]);
        return $ch;
    }
}