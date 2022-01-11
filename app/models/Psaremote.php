<?php

namespace Chell\Models;

use DateInterval;
use DateTime;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to PSA Remote.
 *
 * @package Models
 * @suppress PHP2414
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
        $client = $this->getHttpClient('/get_vehicleinfo/' . $this->_settings->psaremote->vin . ($cache ? '?from_cache=1' : null));
        $result = json_decode($client->getBody());
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

    /**
     * Gets the number of seconds from an iso8601 formatted time interval.
     *
     * @param string $input     The iso8601 formatted time interval as string.
     * @return float            The number of seconds.
     */
    private function iso8601ToSeconds(string $input) : float
    {
        $duration = new DateInterval($input);
        $hours_to_seconds = $duration->h * 60 * 60;
        $minutes_to_seconds = $duration->i * 60;
        $seconds = $duration->s;

        return $hours_to_seconds + $minutes_to_seconds + $seconds;
    }

    /**
     * Gets the ResponseInterface to be used to invoke the PSA Remote API.
     *
     * @param string $url            The PSA Remote endpoint to call.
     * @return ResponseInterface     The ResponseInterface to call the API with.
     */
    private function getHttpClient(string $url) : ResponseInterface
    {
        $client = new Client();
        return $client->request('GET', $this->_settings->psaremote->url . $url,
            ['auth' => [$this->_settings->psaremote->username, $this->_settings->psaremote->password]]);
    }
}