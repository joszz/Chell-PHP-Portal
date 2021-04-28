<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to Jellyfin.
 *
 * @package Modelsz
 */
class Jellyfin extends Model
{
    public static function GetLatest($config, $phalconView)
    {
        $views = self::GetViews($config);

        foreach($views as $view => $viewId)
        {
            $phalconView->{$view} = self::GetLatestForView($config, $viewId);
        }
    }

    /**
     * Retrieves the different channels/libraries defined for a user.
     *
     * @param object $config	The config object representing config.ini.
     * @return array            The array of channels/libraries. The key holds the name lowercase, and removed from <spaces>.
     *                          The value holds the channel's Id.
     */
    public static function GetViews($config)
    {
        $ch = self::getCurl($config, '/Users/' . $config->jellyfin->userid . '/Views');
        $result = [];

        if (($output = curl_exec($ch)) !== false && !empty($output))
        {
            $output = json_decode($output);
            foreach($output->Items as $item)
            {
                if(isset($item->CollectionType))
                {
                    $result[str_replace(' ', '', strtolower($item->CollectionType))] = $item->Id;
                }
            }
        }

        curl_close($ch);
        return $result;
    }

    /**
     * Retrieves the latest items for the fiven $viewId, limiting to $limit.
     *
     * @param object $config	The config object representing config.ini.
     * @param mixed $viewId     The channel/library id to retrieve the latest items to.
     * @param mixed $limit      The limit of items to retrieve. This seems to not always be honored by the API?
     * @return array[]          An array of latest items with some information about this item, such as the title.
     */
    public static function GetLatestForView($config, $viewId, $limit = 10)
    {
        $ch = self::getCurl($config, '/Users/' . $config->jellyfin->userid . '/Items/Latest?limit=' . $limit . '&ParentId=' . $viewId);
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
     * @param object $config	            The config object representing config.ini.
     * @param string $url                   The Jellyfin endpoint to call.
     * @return \CurlHandle|bool|resource    The handle to use to call the Jellyfin API with.
     */
    private static function getCurl($config, $url)
    {
        $ch = curl_init($config->jellyfin->url . $url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['X-MediaBrowser-Token:' .$config->jellyfin->token],
            CURLOPT_TIMEOUT => 3
        ]);
        return $ch;
    }
}