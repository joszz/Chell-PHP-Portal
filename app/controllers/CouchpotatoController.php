<?php

namespace Chell\Controllers;

/**
 * The controller responsible for all CouchPotato related actions.
 *
 * @package Controllers
 */
class CouchPotatoController extends BaseController
{
	/**
     * Set the default layout to empty.
     */
	public function initialize()
	{
		$this->view->setMainView('layouts/empty');
		parent::initialize();
	}

    /**
     * Retrieves movie details from CouchPotato API.
     *
     * @param string $id    The CouchPotate ID to use to call the API with.
     */
    public function movieAction($id)
	{
		$curl = curl_init($this->config->couchpotato->URL . 'api/' . $this->config->couchpotato->APIKey . '/media.get/?id=' . $id);
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 0));
		$content = json_decode(curl_exec($curl));
		curl_close($curl);

		if($content->success) {
			$movie = $content->media;
			$movie->trailer = $this->getRandomTrailerFormTMDB($movie->info->tmdb_id);

			$this->view->bgImage = current($movie->info->images->backdrop_original);
			$this->view->movie = $movie;
		}
	}

	/**
     * Gets a random trailer from TMDB provided a TMDB ID.
     *
     * @param int $id   The TMDB ID to call the API by.
     * @return string   The YouTube ID to be used for iFrame src.
     */
	private function getRandomTrailerFormTMDB($id)
	{
		$curl = curl_init($this->config->application->tmdbAPIURL . 'movie/' . $id . '/videos?api_key=' . $this->config->application->tmdbAPIKey);
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