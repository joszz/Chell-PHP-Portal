<?php

namespace Chell\Controllers;

use DateTime;
use Chell\Controllers\WidgetController;
use Chell\Models\Kodi\KodiMovies;
use Chell\Models\Kodi\KodiAlbums;
use Chell\Models\Kodi\KodiTVShowEpisodes;
use GuzzleHttp\Client;

/**
 * The controller responsible for all actions related to Kodi.
 *
 * @package Controllers
 */
class KodiController extends WidgetController
{
	/**
     * Set the default layout to empty.
     */
	public function initialize()
	{
		$this->SetEmptyLayout();
		$this->view->overflow = true;

		parent::initialize();
	}

    public function addAssets()
    {
        $this->jsFiles[] = 'gallery';
        $this->cssFiles[] = 'gallery';
    }

	/**
     * Show movie details.
     *
     * @param int $id The ID of the movie.
     * @suppress PHP0406
     */
	public function movieAction(int $id)
	{
		$movie = KodiMovies::findFirst([
			'conditions' => 'idMovie = ?1',
			'bind'       => [1 => $id],
		]);

		$movie = current($movie->extractMovieImagesFromXML([$movie]));
		$movie->trailer = substr($movie->c19, strpos($movie->c19, 'videoid=') + 8);

		$this->view->bgImage = $movie->getImageUrl('fanart',  'c20', 'idMovie');
		$this->view->movie = $movie;
	}

	/**
     * Show episode details.
     *
     * @param int $id The ID of the episode.
     * @suppress PHP0406
     */
	public function episodeAction(int $id)
	{
		$episode = KodiTVShowEpisodes::findFirst([
			'conditions' => 'idEpisode = ?1',
			'bind'       => [1 => $id],
		]);

		$episode->show = current($episode->extractMovieImagesFromXML([$episode->show]));
		$this->view->bgImage = $episode->show->getImageUrl('fanart',  'c06', 'idShow');
		$this->view->episode = $episode;
	}

	/**
     * Show album details.
     *
     * @param int $id The ID of the album.
     */
	public function albumAction(int $id)
	{
		$album = KodiAlbums::findFirst([
			'conditions' => 'idAlbum = ?1',
			'bind'       => [1 => $id],
		]);

		$this->view->album = $album;
	}

	/**
     * Gets an external image and caches it locally before it is outputted to the browser.
     *
     * @param string $which		For which library to get the image for.
     * @param string $type		The type of iamge to retrieve (fanart or thumb).
     * @param int $id			The Id of the media record.
     * @param mixed $maxWidth	Whether to resize to image to specified width.
     * @suppress PHP0406
     */
	public function getImageAction(string $which, string $type, int $id, $maxWidth = 'disabled')
	{
		$this->view->disable();

		switch($which) {
			case 'movies':
				$item = KodiMovies::findFirst([
					'conditions' => 'idMovie = ?1',
					'bind'       => [1 => $id],
				]);
				$movie = current($item->extractMovieImagesFromXML([$item]));
				$url = $type == 'thumb' ? $movie->c08 : $movie->c20;
				break;

			case 'albums':
				$item = KodiAlbums::findFirst([
					'conditions' => 'idAlbum = ?1',
					'bind'       => [1 => $id],
				]);
				$url = current($item->extractAlbumImagesFromXML([$item]))->strImage;
				break;

			case 'episodes':
				$item = KodiTVShowEpisodes::findFirst([
					'conditions' => 'idEpisode = ?1',
					'bind'       => [1 => $id],
				]);
				$url = current($item->extractMovieImagesFromXML([$item->show]))->c06;
				break;
		}

		if (isset($url))
		{
			$ntct = ['0' => 'unknown',
					 '1' => 'image/gif',
					 '2' => 'image/jpeg',
					 '3' => 'image/png',
					 '6' => 'image/bmp'];
			$filename = getcwd() . '/img/cache/' . basename($url);

			if (!file_exists($filename))
			{
                $client = new Client();
				$output = $client->request('GET', $url)->getBody();
				
				if (!empty($output))
				{
					file_put_contents($filename, $output);
				}
			}

			$filetype = $ntct[@exif_imagetype($filename)];

			if ($filetype === 'unknown' || !isset($filetype))
			{
				$filename = getcwd() . '/img/icons/unknown.jpg';
				$filetype = $ntct[exif_imagetype($filename)];
			}

			if ($maxWidth != 'disabled')
			{
				$resizedPath = getcwd() . '/img/cache/resized/' . $maxWidth. '/';

				if (!file_exists($resizedPath))
				{
					mkdir($resizedPath);
				}

				$resizedPath .= basename($url);

				if (!file_exists($resizedPath))
				{

					$this->resizeImage($filename, $resizedPath);
				}

				$filename = $resizedPath;
			}

			$expiryDate = new DateTime();
			$expiryDate->modify('+1 year');
			$modifiedDate = new DateTime();
			$modifiedDate->setTimestamp(filemtime($filename));

			$this->response->setCache(60 * 60 * 24 * 365);
			$this->response->setExpires($expiryDate);
			$this->response->setLastModified($modifiedDate);
			$this->response->setContentType($filetype);
			$this->response->setHeader('Pragma', 'cache');

			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			{
				$this->response->setNotModified();
			}

			$this->response->setContent(readfile($filename))->send();
		}
	}
}