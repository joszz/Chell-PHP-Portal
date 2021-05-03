<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to Jellyfin.
 *
 * @package Modelsz
 */
class Jellyfin extends BaseModel
{
    /**
     * Retrieves the different channels/libraries defined for a user.
     *
     * @return array            The array of channels/libraries. The key holds the name lowercase, and removed from <spaces>.
     *                          The value holds the channel's Id.
     */
    public function getViews()
    {
        $ch = $this->getCurl('/Users/' . $this->_config->jellyfin->userid . '/Views');
        $result = [];

        if (($output = curl_exec($ch)) !== false && !empty($output))
        {
            $output = json_decode($output);
            foreach($output->Items as $item)
            {
                $result[$item->Name] = $item->Id;
            }
        }

        ksort($result);
        curl_close($ch);
        return $result;
    }

    /**
     * Retrieves the latest items for the fiven $viewId, limiting to $limit.
     *
     * @param mixed $viewId     The channel/library id to retrieve the latest items to.
     * @param mixed $limit      The limit of items to retrieve. This seems to not always be honored by the API?
     * @return array          An array of latest items with some information about this item, such as the title.
     */
    public function getLatestForView($viewId, $limit = 10)
    {
        $ch = self::getCurl('/Users/' . $this->_config->jellyfin->userid . '/Items/Latest?limit=' . $limit . '&ParentId=' . $viewId);
        $result = [];
        if (($output = curl_exec($ch)) !== false && !empty($output))
        {
            $output = json_decode($output);

            foreach($output as $item)
            {
                $result[] = [
                    'id' => $item->Id,
                    'serverId' => $item->ServerId,
                    'title' => $item->Name,
                    'played' => isset($item->PlayCount) ? $item->PlayCount > 0 : false,
                    'year' => isset($item->ProductionYear) ? $item->ProductionYear : false,
                    'artist' => isset($item->Artists) && count($item->Artists) ? $item->Artists[0] : ''
                ];
            }
        }

        curl_close($ch);
        return $result;
    }

    /**
     * Gets the CurlHandle to be used to invoke the Jellyfin API.
     *
     * @param string $url                   The Jellyfin endpoint to call.
     * @return \CurlHandle|bool|resource    The handle to use to call the Jellyfin API with.
     */
    private function getCurl($url)
    {
        $ch = curl_init($this->_config->jellyfin->url . $url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['X-MediaBrowser-Token:' .$this->_config->jellyfin->token],
            CURLOPT_TIMEOUT => 3
        ]);
        return $ch;
    }
}