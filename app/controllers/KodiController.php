<?php

namespace Chell\Controllers;

use Chell\Models\Kodi\KodiMovies;
use Chell\Models\Kodi\KodiMusic;
use Chell\Models\Kodi\KodiTVShowEpisodes;

/**
 * The controller responsible for all actions related to Kodi.
 *
 * @package Controllers
 */
class KodiController extends BaseController
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
     * Show movie details.
     *
     * @param int $id     The ID of the movie.
     */
    public function movieAction($id)
    {
        $movie = KodiMovies::findFirst(array(
            'conditions' => 'idMovie = ?1',
            'bind'       => array(1 => $id),
        ));
        $movie = current(KodiMovies::extractMovieImagesFromXML(array($movie)));
        $movie->trailer = substr($movie->c19, strpos($movie->c19, 'videoid=') + 8);

        $this->view->bgImage = '../getImage/movies/fanart/' . $movie->idMovie . '/800';
        $this->view->movie = $movie;
    }

    /**
     * Show episode details.
     *
     * @param int $id     The ID of the episode.
     */
    public function episodeAction($id)
    {
        $episode = KodiTVShowEpisodes::findFirst(array(
            'conditions' => 'idEpisode = ?1',
            'bind'       => array(1 => $id),
        ));

        $this->view->bgImage = '../getImage/episodes/thumb/' . $episode->idEpisode;
        $this->view->episode = $episode;
    }

    /**
     * Show album details.
     *
     * @param int $id     The ID of the album.
     */
    public function albumAction($id)
    {
        $album = KodiMusic::findFirst(array(
            'conditions' => 'idAlbum = ?1',
            'bind'       => array(1 => $id),
        ));

        $this->view->album = $album;
    }

    /**
     * Gets an external image and caches it locally before it is outputted to the browser.
     */
    public function getImageAction($which, $type, $id, $maxWidth = 'disabled')
    {
        switch($which) {
            case 'movies':
                $item = KodiMovies::findFirst(array(
                    'conditions' => 'idMovie = ?1',
                    'bind'       => array(1 => $id),
                ));
                $movie = current(KodiMovies::extractMovieImagesFromXML(array($item)));
                $url = $type == 'thumb' ? $movie->c08 : $movie->c20;
                break;

            case 'albums':
                $item = KodiMusic::findFirst(array(
                    'conditions' => 'idAlbum = ?1',
                    'bind'       => array(1 => $id),
                ));
                $url = current(KodiMusic::extractAlbumImagesFromXML(array($item)))->strImage;
                break;

            case 'episodes':
                $item = KodiTVShowEpisodes::findFirst(array(
                    'conditions' => 'idEpisode = ?1',
                    'bind'       => array(1 => $id),
                ));
                $url = current(KodiTVShowEpisodes::extractMovieImagesFromXML(array($item)))->c06;
                break;
        }

        if(isset($url)) 
        {
            $ntct = Array('0' => 'unknown',
						  '1' => 'image/gif',
                          '2' => 'image/jpeg',
                          '3' => 'image/png',
                          '6' => 'image/bmp');
            $filename = getcwd() . '/img/cache/' . basename($url);
				
			if(!file_exists($filename)) 
			{
				$ch = curl_init($url);
				curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS => 5,
					CURLOPT_TIMEOUT => 3
				));

				if(($output = curl_exec($ch)) !== false && empty($output)) 
				{
					file_put_contents($filename, $output);
				}
				
				curl_close($ch);
			}

			$filetype = $ntct[@exif_imagetype($filename)];
			
			if($filetype === 'unknown' || !isset($filetype))
			{
				$filename = getcwd() . '/img/icons/unknown.jpg';
				$filetype = $ntct[exif_imagetype($filename)];
			}

			if($maxWidth != 'disabled') 
			{
				$resizedPath = getcwd() . '/img/cache/resized/' . $maxWidth. '/';

				if(!file_exists($resizedPath))
				{
					mkdir($resizedPath);
				}

				$resizedPath .= basename($url);

				if(!file_exists($resizedPath))
				{
					
					$this->resizeImage($filename, $resizedPath);
				}

				$filename = $resizedPath;
			}

			header('Cache-control: max-age='.(60 * 60 * 24 * 365));
			header('Expires: '.gmdate(DATE_RFC1123 ,time()+ 60 * 60 * 24 * 365));
			header('Last-Modified: '.gmdate(DATE_RFC1123, filemtime($filename)));
			header('Content-type: ' . $filetype);
			header("Pragma: cache");

			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			{
				header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
			}

			die(readfile($filename));
		
        }
    }
}