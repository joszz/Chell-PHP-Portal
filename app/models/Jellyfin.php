<?php

namespace Chell\Models;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to Jellyfin.
 *
 * @package Models
 * @suppress PHP2414
 */
class Jellyfin extends BaseModel
{
    /**
     * Retrieves the different channels/libraries defined for a user.
     *
     * @return array            The array of channels/libraries. The key holds the name lowercase, and removed from <spaces>.
     *                          The value holds the channel's Id.
     */
    public function getViews() : array
    {
        $response = $this->getHttpClient('/Users/' . $this->_settings->jellyfin->userid . '/Views');
        $output = $response->getBody();
        $result = [];

        if (!empty($output))
        {
            $output = json_decode($output);
            foreach($output->Items as $item)
            {
                $result[$item->Name] = strtolower($item->Name) . ':' . $item->Id;
            }
        }

        ksort($result);
        return $result;
    }

    /**
     * Retrieves the latest items for the fiven $viewId, limiting to $limit.
     *
     * @param string $viewId     The channel/library id to retrieve the latest items to.
     * @param int $limit         The limit of items to retrieve. This seems to not always be honored by the API?
     * @return array             An array of latest items with some information about this item, such as the title.
     */
    public function getLatestForView(string $viewId, int $limit = 10) : array
    {
        $response = $this->getHttpClient('/Users/' . $this->_settings->jellyfin->userid . '/Items/Latest?limit=' . $limit . '&ParentId=' . $viewId);
        $output = $response->getBody();
        $result = [];
        $count = 0;

        if (!empty($output))
        {
            $output = json_decode($output);

            foreach($output as $item)
            {
                $result[$count] = [
                    'id'        => $item->Id,
                    'serverId'  => $item->ServerId,
                    'title'     => $item->Name,
                    'subtitle'  => $item->ProductionYear ?? false,
                    'played'    => $item->UserData->Played ?? false
                ];

                switch (strtolower($item->Type))
                {
                    case 'episode':
                        $result[$count]['title'] = $item->SeriesName;
                        $result[$count]['subtitle'] = $item->Name;
                        break;
                    case 'musicalbum':
                        $result[$count]['title'] = implode(', ', $item->Artists);
                        $result[$count]['played'] = false;
                        $result[$count]['subtitle'] = $item->Name;
                        break;
                }

                $count++;
            }
        }

        return $result;
    }

    /**
     * Gets the ResponseInterface to be used to invoke the PSA Remote API.
     *
     * @param string $url            The PSA Remote endpoint to call.
     * @return ResponseInterface     The ResponseInterface to call the API with.
     */
    private function getHttpClient(string $url) : ResponseInterface
    {
        $client = new Client(['headers' => ['X-MediaBrowser-Token' => $this->_settings->jellyfin->token]]);
        return $client->request('GET', $this->_settings->jellyfin->url . $url);
    }
}