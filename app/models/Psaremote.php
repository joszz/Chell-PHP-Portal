<?php

namespace Chell\Models;

use CurlHandle;
use DateInterval;
use DateTime;

/**
 * The model responsible for all actions related to PSA Remote.
 *
 * @package Models
 */
class Psaremote extends BaseModel
{
    /**
     * Retrieves the vehicle information from the PSA remote API and returns it as an object.
     *
     * @param bool $cache   Whether or not to retrieve the information from cache.
     * @return mixed        An object with the vehicle information.
     */
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
                if ($energy->charging->remaining_time)
                {
                    $now = new DateTime();
                    $updated_at = new DateTime($energy->updated_at);
                    $energy->charging->remaining_time = $this->iso8601ToSeconds($energy->charging->remaining_time) - $updated_at->diff($now)->s;
                }

                $output->energy = $energy;
            }
        }

        return $output;
    }

    private function iso8601ToSeconds($input)
    {
        $duration = new DateInterval($input);
        $hours_to_seconds = $duration->h * 60 * 60;
        $minutes_to_seconds = $duration->i * 60;
        $seconds = $duration->s;

        return $hours_to_seconds + $minutes_to_seconds + $seconds;
    }

    /**
     * Gets the CurlHandle to be used to invoke the PSA Remote API.
     *
     * @param string $url                   The PSA Remote endpoint to call.
     * @return CurlHandle|bool|resource     The handle to use to call the Jellyfin API with.
     */
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