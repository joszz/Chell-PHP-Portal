<?php

namespace Chell\Models;

use Exception;
use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to Sonos.
 *
 * @package Models
 * @suppress PHP2414
 */
class Sonos extends BaseModel
{
    private string $apiOauthAccessUrl = 'https://api.sonos.com/login/v3/oauth/access';
    private string $apiControlUrl = 'https://api.ws.sonos.com/control/api/v1/';

    /**
     * Calls the Sonos API to get an access token, used to call the API with.
     *
     * @param string $code      The OAuth2 code in the querystring parameters of the redirect by the Sonos API.
     * @return bool             True on success or false on failure.
     */
    public function setAccessToken(string $code)
    {
        $options = [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->settings->getDomainWithProtocol() . '/settings/sonos/'
            ]
        ];

        $content = $this->getHttpClient($this->apiOauthAccessUrl, 'POST', $this->getBasicAuthorization(), $options);
        return $this->setTokenSettings($content);
    }

    /**
     * Gets a new access token using the refresh token from the Sonos API.
     *
     * @return bool True on success or false on failure.
     */
    public function refreshAccessToken()
    {
        $options = [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->settings->sonos->refresh_token
            ]
        ];

        $content = $this->getHttpClient($this->apiOauthAccessUrl, 'POST', $this->getBasicAuthorization(), $options);
        return $this->setTokenSettings($content);
    }

    /**
     * Retrieves the configured households from the Sonos API.
     *
     * @return array    An array of households.
     */
    public function getHouseholds()
    {
        $content = $this->getHttpClient($this->apiControlUrl . 'households', 'GET', $this->getBearerAuthorization());
        $result = [];

        foreach ($content->households as $household)
        {
            $result[$household->id] = $household->name;
        }

        return $result;
    }

    /**
     * Retrieves the configured groups from the Sonos API for the given household.
     *
     * @param string $householdId   The household id to get the groups for.
     * @return array                An array of groups.
     */
    public function getGroups(string $householdId)
    {
        $content = $this->getHttpClient($this->apiControlUrl . 'households/' . $householdId . '/groups', 'GET', $this->getBearerAuthorization());
        $result = [];

        foreach ($content->groups as $group)
        {
            $result[$group->id] = $group->name;
        }

        return $result;
    }

    /**
     * Retrieves the currently playing details from the Sonos API.
     *
     * @return Object   An object with all the Sonos playing details.
     */
    public function getPlayingDetails()
    {
        $result = $this->getPlaybackStatus($this->settings->sonos->group_id);
        $metadata = $this->getPlaybackMetadata($this->settings->sonos->group_id);

        $result->track = $metadata->currentItem->track->name ?? '';
        $result->tracknumber = $metadata->currentItem->track->trackNumber ?? '';
        $result->artist = $metadata->currentItem->track->album->artist->name ?? '';
        $result->album = $metadata->currentItem->track->album->name ?? '';

        if (isset($metadata->currentItem->track->imageUrl) && $metadata->container->imageUrl !== 'tracks')
        {
            $result->image = urlencode($metadata->currentItem->track->imageUrl);
        }

        return $result;
    }

    public function getPlaybackMetadata(string $groupId)
    {
        return $this->getHttpClient($this->apiControlUrl . 'groups/' . $groupId . '/playbackMetadata', 'GET', $this->getBearerAuthorization());
    }

    public function getPlaybackStatus(string $groupId)
    {
        return $this->getHttpClient($this->apiControlUrl . 'groups/' . $groupId . '/playback', 'GET', $this->getBearerAuthorization());
    }

    private function getBasicAuthorization()
    {
        return 'Basic ' . base64_encode($this->settings->sonos->api_key . ':' . $this->settings->sonos->api_secret);
    }

    private function getBearerAuthorization()
    {
        return 'Bearer ' . $this->settings->sonos->access_token;
    }

    private function getHttpClient(string $url, string $method, string $authorization, array $options = [])
    {
        $client = new Client(['headers' => ['Authorization' => $authorization]]);

        try
        {
            $response = $client->request($method, $url, $options);
            return json_decode($response->getBody()->getContents());
        }
        catch(Exception $exception)
        {
            $this->logger->LogException($exception);
            return false;
        }
    }

    private function setTokenSettings($content)
    {
        if ($content)
        {
            //tokens need to be encrypted
            $this->settings->sonos->access_token = $content->access_token;
            $this->settings->sonos->refresh_token = $content->refresh_token;
            $this->settings->sonos->token_expires = time() + $content->expires_in;
            $this->settings->save('dashboard');
            return true;
        }
        return false;
    }
}