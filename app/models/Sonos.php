<?php

namespace Chell\Models;

use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Sonos extends BaseModel
{
    private string $apiOauthAccessUrl = 'https://api.sonos.com/login/v3/oauth/access';
    private string $apiControlUrl = 'https://api.ws.sonos.com/control/api/v1/';

    public function setAccessToken(string $code)
    {
        $options = [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::getDomainWithProtocol() . '/settings/sonos/'
            ]
        ];

        $content = $this->getHttpClient($this->apiOauthAccessUrl, 'POST', $this->getBasicAuthorization(), $options);

        if ($content)
        {
            $this->settings->sonos->access_token = $content->access_token;
            $this->settings->sonos->refresh_token = $content->refresh_token;
            $this->settings->sonos->token_expires = time() + $content->expires_in;
            $this->settings->save('dashboard');
            return true;
        }
        return false;
    }

    public function refreshAccessToken()
    {
        $options = [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'refresh_token' => $this->settings->sonos->refresh_token
            ]
        ];

        $content = $this->getHttpClient($this->apiOauthAccessUrl, 'POST', $this->getBasicAuthorization(), $options);

        if ($content)
        {
            $this->settings->sonos->access_token = $content->access_token;
            $this->settings->sonos->refresh_token = $content->refresh_token;
            $this->settings->sonos->token_expires = time() + $content->expires_in;
            $this->settings->save('dashboard');
            return true;
        }
        return false;
    }

    public function getHouseholds()
    {
        $content = $this->getHttpClient($this->apiControlUrl . 'households', 'GET', $this->getBearerAuthorization());
        \Chell\dump($content);
        return true;
    }

    public function getGroups()
    {
        $client = new Client(['headers' => ['Authorization' => 'Bearer  ' . $this->settings->sonos->access_token]]);

        try
        {
            $response = $client->request('GET', 'https://api.ws.sonos.com/control/api/v1/households/Sonos_FdcP4VFspdiTnohOozoGkOatuU.zJHyW9AjdpWbvDRU9cNa/groups');

            $content = json_decode($response->getBody()->getContents());
            \Chell\dump($content);
            return true;
        }
        catch(Exception $exception)
        {
            \Chell\dump($exception->getResponse()->getBody()->getContents());
            $this->logger->LogException($exception);
            return false;
        }
    }

    public function getPlaybackMetadata()
    {
        $client = new Client(['headers' => ['Authorization' => 'Bearer  ' . $this->settings->sonos->access_token]]);

        try
        {
            $response = $client->request('GET', 'https://api.ws.sonos.com/control/api/v1/groups/RINCON_38420B70F68C01400:996849727/playbackMetadata');

            $content = json_decode($response->getBody()->getContents());
            \Chell\dump($content);
            return true;
        }
        catch(Exception $exception)
        {
            \Chell\dump($exception->getResponse()->getBody()->getContents());
            $this->logger->LogException($exception);
            return false;
        }
    }

    public function getPlaybackStatus()
    {
        $content = $this->getHttpClient($this->apiControlUrl . 'groups/RINCON_38420B70F68C01400:996849727/playback', 'GET', $this->getBearerAuthorization());
        \Chell\dump($content);
        return true;
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
}