<?php

namespace Chell\Models;

use GuzzleHttp\Client;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class Couchpotato extends BaseModel
{
	/**
     * Retrieves all wanted movies from CouchPotat API.
     *
     * @return array|bool            The movies objects as an array or false on failure.
     */
	public function getAllMovies()
	{
		$content = $this->getHttpClientBody($this->_settings->couchpotato->url . 'api/' . $this->_settings->couchpotato->api_key . '/media.list');

		return $content && $content->success ? $content->movies : false;
	}

	/**
     * Retrieves movie details from CouchPotato API.
     *
     * @param string $id        The CouchPotate ID to use to call the API with.
     * @return mixed            The movie object or false when API call is unsuccessful.
     */
	public function getMovie(string $id)
	{
		$movie = false;
		$content = $this->getHttpClientBody($this->_settings->couchpotato->url . 'api/' . $this->_settings->couchpotato->api_key . '/media.get/?id=' . $id);

		if ($content->success)
		{
			$movie = $content->media;
			$movie->trailer = $this->getRandomTrailerFormTMDB($movie->info->tmdb_id);
		}

		return $movie;
	}

	/**
     * Gets a random trailer from TMDB provided a TMDB ID.
     *
     * @param int $id           The TMDB ID to call the API by.
     * @return string           The YouTube ID to be used for iFrame src.
     */
	private function getRandomTrailerFormTMDB(int $id) : string
	{
		$content = $this->getHttpClientBody($this->_settings->application->tmdb_api_url . 'movie/' . $id . '/videos?api_key=' . $this->_settings->application->tmdb_api_key);
		$randomTrailerIndex = array_rand($content->results);

		return isset($content->results[$randomTrailerIndex]) ? $content->results[$randomTrailerIndex]->key : '';
	}

    private function getHttpClientBody(string $url)
    {
        $client = new Client();
        $response = $client->request('GET', $url);

		return json_decode($response->getBody());
    }
}