<?php

namespace Chell\Models;

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
     * @return array            The movies objects as an array.
     */
	public function getAllMovies()
	{
		$curl = curl_init($this->_config->couchpotato->URL . 'api/' . $this->_config->couchpotato->APIKey . '/media.list');
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 0
		]);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		return $content && $content->success ? $content->movies : false;
	}

	/**
     * Retrieves movie details from CouchPotato API.
     *
     * @param string $id        The CouchPotate ID to use to call the API with.
     * @return mixed            The movie object or false when API call is unsuccessful.
     */
	public function getMovie($id)
	{
		$movie = false;
		$curl = curl_init($this->_config->couchpotato->URL . 'api/' . $this->_config->couchpotato->APIKey . '/media.get/?id=' . $id);
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 0
		]);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

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
	private function getRandomTrailerFormTMDB($id)
	{
		$curl = curl_init($this->_config->application->tmdbAPIURL . 'movie/' . $id . '/videos?api_key=' . $this->_config->application->tmdbAPIKey);
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 0
		]);
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		$randomTrailerIndex = array_rand($content->results);

		return isset($content->results[$randomTrailerIndex]) ? $content->results[$randomTrailerIndex]->key : '';
	}
}