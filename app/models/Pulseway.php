<?php

namespace Chell\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * The model responsible for all actions related to Pulseway.
 *
 * @see https://api.pulseway.com/
 * @package Models
 */
class Pulseway extends BaseModel
{
    /**
     * Retrieves all the systems defined in Pulseway
     * @return array    The systems, where key is the identifier and value is the name.
     */
    public function getSystems() : array
    {
        $result = [];
        $content = $this->getHttpClient('/systems');

        if ($content)
        {
            foreach($content->data as $system)
            {
                $result[$system->identifier] = $system->name;
            }
        }

        return $result;
    }

    /**
     * Retrieves the information specific to a system.
     *
     * @param string $id    The System identifier.
     * @return mixed        Either the data retrieved or false on failure.
     */
    public function getSystem(string $id)
    {
        $data = $this->getHttpClient('/systems/' . $id);
        return $data ? $data->data : false;
    }

    /**
     * Retrieves the assets specific to a system.
     *
     * @param string $id    The System identifier.
     * @return mixed        Either the data retrieved or false on failure.
     */
    public function getAssets(string $id)
    {
        $data = $this->getHttpClient('/assets/' . $id);
        return $data ? $data->data : false;
    }

    /**
     * Calls the Pulseway API $url and retrieves the content.
     *
     * @param string $url                   The Pulseway endpoint to call.
     * @param bool $decode                  Whether or not to JSON decode the requested data.
     * @return string|object                The Pulseway data, either in object or string form.
     */
    private function getHttpClient(string $url, bool $decode = true)
    {
        if (empty($this->settings->pulseway->username->value) || empty($this->settings->pulseway->password->value) || empty($this->settings->pulseway->url->value))
        {
            return false;
        }

        $client = new Client();

        try
        {
            $response = $client->request('GET', $this->settings->pulseway->url->value . $url,
                ['auth' => [$this->settings->pulseway->username->value, $this->settings->pulseway->password->value]]);
            $content = $response->getBody();
            return $decode ? json_decode($content) : $content;
        }
        catch(ClientException $exception)
        {
            $this->logger->LogException($exception);
            return false;
        }
    }
}