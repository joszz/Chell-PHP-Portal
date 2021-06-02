<?php

namespace Chell\Models;

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
    public function getSystems()
    {
        $result = [];
        $content = $this->callApi('systems');
        
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
    public function getSystem($id)
    {
        $data = $this->callApi('systems/' . $id);
        return $data ? $data->data : false;
    }

    /**
     * Retrieves the assets specific to a system.
     *
     * @param string $id    The System identifier.
     * @return mixed        Either the data retrieved or false on failure.
     */
    public function getAssets($id)
    {
        $data = $this->callApi('assets/' . $id);
        return $data ? $data->data : false;
    }

    /**
     * Gets the CurlHandle to be used to invoke the Pulseway API.
     *
     * @param string $url                   The Pulseway endpoint to call.
     * @param bool $decode                  Whether or not to JSON decode the requested data.
     * @return string|object                The Pulseway data, either in object or string form.
     */
    private function callApi($url, $decode = true)
    {
        if (empty($this->_settings->pulseway->username) || empty($this->_settings->pulseway->password) || empty($this->_settings->pulseway->url))
        {
            return false;
        }

        $ch = curl_init($this->_settings->pulseway->url . $url);
        curl_setopt_array($ch, [
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERPWD => $this->_settings->pulseway->username . ":" . $this->_settings->pulseway->password
        ]);
        $content = curl_exec($ch);
        curl_close($ch);

        if ($decode)
        {
            $content = json_decode($content);
        }

        return $content;
    }
}