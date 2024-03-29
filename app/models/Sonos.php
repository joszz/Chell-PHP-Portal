<?php

namespace Chell\Models;

use Exception;
use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to Sonos.
 *
 * @package Models
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
                'refresh_token' => $this->settings->sonos->refresh_token->value
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

        if ($content)
        {
            foreach ($content->households as $household)
            {
                $result[$household->name] = $household->name;
            }
        }

        return $result;
    }

    private function getHouseholdIdByName($name)
    {
        $content = $this->getHttpClient($this->apiControlUrl . 'households', 'GET', $this->getBearerAuthorization());

        if ($content)
        {
            foreach ($content->households as $household)
            {
                if ($household->name == $name)
                {
                    return $household->id;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves the configured groups from the Sonos API for the given household.
     *
     * @param string $household   The household id to get the groups for.
     * @return array                An array of groups.
     */
    public function getGroups(string $household)
    {
        $householdId = $this->getHouseholdIdByName($household);
        $content = $this->getHttpClient($this->apiControlUrl . 'households/' . $householdId . '/groups', 'GET', $this->getBearerAuthorization());
        $result = [];

        if ($content)
        {
            foreach ($content->groups as $group)
            {
                $result[$group->name] = $group->name;
            }
        }

        return $result;
    }

    private function getGroupIdByName($name)
    {
        $householdId = $this->getHouseholdIdByName($this->settings->sonos->household->value);

        if ($householdId)
        {
            $content = $this->getHttpClient($this->apiControlUrl . 'households/' . $householdId . '/groups', 'GET', $this->getBearerAuthorization());

            foreach ($content->groups as $group)
            {
                if ($group->name == $name)
                {
                    return $group->id;
                }
            }
        }
        return false;
    }

    /**
     * Retrieves the currently playing details from the Sonos API.
     *
     * @return bool|\stdClass   An object with all the Sonos playing details or false on failure.
     */
    public function getPlayingDetails()
    {
        $groupId = $this->getGroupIdByName($this->settings->sonos->group->value);

        if ($groupId)
        {
            $result = $this->getPlaybackStatus($groupId);
            $metadata = $this->getPlaybackMetadata($groupId);

            if ($metadata)
            {
                $result->track = $metadata->currentItem->track->name ?? '';
                $result->tracknumber = $metadata->currentItem->track->trackNumber ?? '';
                $result->artist = $metadata->currentItem->track->album->artist->name ?? '';
                $result->album = $metadata->currentItem->track->album->name ?? '';

                if (isset($metadata->container->imageUrl))
                {
                    $result->image = urlencode($metadata->container->imageUrl);
                }
                else if (isset($metadata->currentItem->track->imageUrl))
                {
                    $result->image = urlencode($metadata->currentItem->track->imageUrl);
                }
            }

            return $result;
        }

        return false;
    }

    /**
     * Calls the Sonos API to retrieve the playback metadata.
     *
     * @param string $groupId   The group to retrieve playback metadata for.
     * @return mixed            An object containing the playback metadata.
     */
    public function getPlaybackMetadata(string $groupId)
    {
        return $this->getHttpClient($this->apiControlUrl . 'groups/' . $groupId . '/playbackMetadata', 'GET', $this->getBearerAuthorization());
    }

    /**
     * Calls the Sonos API to retrieve the playback status.
     *
     * @param string $groupId   The group to retrieve playback status for.
     * @return mixed            An object containing the playback status.
     */
    public function getPlaybackStatus(string $groupId)
    {
        return $this->getHttpClient($this->apiControlUrl . 'groups/' . $groupId . '/playback', 'GET', $this->getBearerAuthorization());
    }

    /**
     * Retrieves the URL/IP from the websocketUrl and saves this to the settings.
     * Only called once by the SonosController when the setting is not set.
     * The URL/IP is consequently used to retrieve images which have relative URLs.
     */
    public function setUrl()
    {
        $content = $this->getHttpClient($this->apiControlUrl . 'households/' . $this->settings->sonos->household_id->value . '/groups', 'GET', $this->getBearerAuthorization());
        $url = str_replace('wss://', '', current($content->players)->websocketUrl);
        $url = substr($url, 0, stripos($url, ':'));
        $this->settings->sonos->url->value = $url;
        $this->settings->save('dashboard');
    }

    /**
     * Gets a Basic Auth string to be used to retrieve a token from the Sonos API.
     *
     * @return string   The Basic auth string.
     */
    private function getBasicAuthorization()
    {
        return 'Basic ' . base64_encode($this->settings->sonos->api_key->value . ':' . $this->settings->sonos->api_secret->value);
    }

    /**
     * Gets the bearer token to do Sonos API calls with.
     *
     * @return string   The bearer token
     */
    private function getBearerAuthorization()
    {
        return 'Bearer ' . $this->settings->sonos->access_token->value;
    }

    /**
     * Get s a new HTTP Client to do Sonos API calls with.
     *
     * @param string $url               The Sonos endpoint to call.
     * @param string $method            The request method, either 'GET' or 'POST'
     * @param array $options            The options for the request, such as specifying the POST form parameters.
     * @return mixed                    Either a object converted from the API's JSON response, or false on failure.
     */
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

    /**
     * Persists the token related fields in the database for future use.
     *
     * @param mixed $content    The object containing the tokens.
     * @return bool             Either trye on success or false on failure.
     */
    private function setTokenSettings($content)
    {
        if ($content)
        {
            //tokens need to be encrypted
            $this->settings->sonos->access_token->value = $content->access_token;
            $this->settings->sonos->refresh_token->value = $content->refresh_token;
            $this->settings->sonos->token_expires->value = time() + $content->expires_in;
            $this->settings->save('dashboard');
            return true;
        }
        return false;
    }
}