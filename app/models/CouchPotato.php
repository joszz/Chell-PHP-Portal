<?php

namespace Chell\Models;

use Phalcon\Mvc\Model;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 */
class CouchPotato extends Model
{

	public function getAllMovies($config)
	{
		$curl = curl_init($config->couchpotato->URL . 'api/' . $config->couchpotato->APIKey . '/media.list');
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 0));
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

        if($content->success){
            return $content->movies;
        }

        return false;
	}

	/**
     * Retrieves movie details from CouchPotato API.
     *
     * @param string $id        The CouchPotate ID to use to call the API with.
     * @param array $config     The config.ini as array.
     * @return mixed            The movie object or false when API call is unsuccessful.
     */
	public function getMovie($id, $config)
	{
		$curl = curl_init($config->couchpotato->URL . 'api/' . $config->couchpotato->APIKey . '/media.get/?id=' . $id);
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 0));
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		if($content->success) {
			$movie = $content->media;
			$movie->trailer = self::getRandomTrailerFormTMDB($movie->info->tmdb_id, $config);

			return $movie;
		}

		return false;
	}

	/**
     * Gets a random trailer from TMDB provided a TMDB ID.
     *
     * @param int $id           The TMDB ID to call the API by.
     * @param array $config     The config.ini as array.
     * @return string           The YouTube ID to be used for iFrame src.
     */
	private function getRandomTrailerFormTMDB($id, $config)
	{
		$curl = curl_init($config->application->tmdbAPIURL . 'movie/' . $id . '/videos?api_key=' . $config->application->tmdbAPIKey);
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 0));
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		$randomTrailerIndex = array_rand($content->results);

		if(isset($content->results[$randomTrailerIndex])) {
			return $content->results[$randomTrailerIndex]->key;
		}

		return '';
	}
}