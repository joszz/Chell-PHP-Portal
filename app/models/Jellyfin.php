<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to Verisure.
 *
 * @see https://github.com/persandstrom/python-verisure
 * @package Models
 */
class Jellyfin extends Model
{
    public static function GetViews($config)
    {
        $ch = self::getCurl($config, '/Users/' . $config->jellyfin->userid . '/Views');
        $result = [];

        if (($output = curl_exec($ch)) !== false && !empty($output))
        {
            $output = json_decode($output);
            foreach($output->Items as $item)
            {
                $result[str_replace(' ', '', strtolower($item->Name))] = $item->Id;
            }
        }

        curl_close($ch);
        return $result;
    }

    public static function GetLatest($config, $viewId, $limit = 10)
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
                    'year' => $item->ProductionYear,
                    'artist' => isset($item->Artists) ? $item->Artists[0] : ''
                ];
            }
        }

        curl_close($ch);
        return $result;
    }

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