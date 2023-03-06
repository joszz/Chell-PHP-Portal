<?php

namespace Chell\Models;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The model responsible for all actions related to Jellyfin.
 *
 * @package Models
 */
class Jellyfin extends BaseModel
{
    /**
     * Retrieves the different channels/libraries defined for a user.
     *
     * @return array    The array of channels/libraries. The key holds the name lowercase, and removed from <spaces>.
     *                  The value holds the channel's Id.
     */
    public function getViews() : array
    {
        $result = [];

        try
        {
            $response = $this->getHttpClient('/Users/' . $this->settings->jellyfin->userid . '/Views', false);
        }
        catch (Exception $exception)
        {
            $this->logger->LogException($exception);
            return $result;
        }

        $output = $response->getBody();

        if (!empty($output))
        {
            $output = json_decode($output);
            foreach($output->Items as $item)
            {
                $result[$item->Name] = strtolower($item->Name) . ':' . $item->Id;
            }
        }

        ksort($result);
        return array_flip($result);
    }

    /**
     * Retrieves the latest items for the fiven $viewId, limiting to $limit.
     *
     * @param string $viewId     The channel/library id to retrieve the latest items to.
     * @param int $limit         The limit of items to retrieve. This seems to not always be honored by the API?
     * @return array             An array of latest items with some information about this item, such as the title.
     */
    public function getLatestForViews(int $limit = 10) : array
    {
        $views = explode(',', $this->settings->jellyfin->views);
        $promises = [];

        foreach ($views as $view)
        {
            list($title, $viewId) = explode(':', $view);
            $promises[$title] = $this->getHttpClient('/Users/' . $this->settings->jellyfin->userid . '/Items/Latest?limit=' . $limit . '&ParentId=' . $viewId, true);
        }

        $result = [];
        $responses = Promise\Utils::settle($promises)->wait();

        foreach ($responses as $title => $response)
        {
            $output = $response['value']->getBody();
            $count = 0;
            $viewResult = [];

            if (!empty($output))
            {
                $output = json_decode($output);

                foreach($output as $item)
                {
                    $viewResult[$count] = [
                        'id'        => $item->Id,
                        'serverId'  => $item->ServerId,
                        'title'     => $item->Name,
                        'subtitle'  => $item->ProductionYear ?? false,
                        'played'    => $item->UserData->Played ?? false
                    ];

                    switch (strtolower($item->Type))
                    {
                        case 'episode':
                            $viewResult[$count]['title'] = $item->SeriesName;
                            $viewResult[$count]['subtitle'] = $item->Name;
                            break;
                        case 'musicalbum':
                            $viewResult[$count]['title'] = implode(', ', $item->Artists);
                            $viewResult[$count]['played'] = false;
                            $viewResult[$count]['subtitle'] = $item->Name;
                            break;
                    }

                    $count++;
                }
            }

            $result[$title] = $viewResult;
        }

        return $result;
    }

    /**
     * Gets the ResponseInterface to be used to invoke the PSA Remote API.
     *
     * @param string $url           The PSA Remote endpoint to call.
     * @return PromiseInterface     The PromiseInterface to call the API with.
     */
    private function getHttpClient(string $url, bool $async) : PromiseInterface | ResponseInterface
    {
        $client = new Client(['headers' => ['X-MediaBrowser-Token' => $this->settings->jellyfin->token]]);
        return $client->{ 'request' . ($async ? 'Async' : null) }('GET', $this->settings->jellyfin->url . $url);
    }
}