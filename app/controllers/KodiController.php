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
                $album = current(KodiMusic::extractAlbumImagesFromXML(array($item)));
                $url = $type == 'thumb' ? $album->strImage : $album->strImage;
                break;

            case 'episodes':
                $item = KodiTVShowEpisodes::findFirst(array(
                    'conditions' => 'idEpisode = ?1',
                    'bind'       => array(1 => $id),
                ));
                $episode = current(KodiTVShowEpisodes::extractMovieImagesFromXML(array($item)));
                $url = $type == 'thumb' ? $episode->c06 : $episode->c06;
                break;
        }

        if(isset($url)) {
            $ntct = Array('1' => 'image/gif',
                          '2' => 'image/jpeg',
                          '3' => 'image/png',
                          '6' => 'image/bmp');
            $filename = getcwd() . '/img/cache/' . basename($url);

            if(!file_exists($filename)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                file_put_contents($filename, $output);
            }

            if($maxWidth != 'disabled') {
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

            session_cache_limiter('none');
            header('Cache-control: max-age='.(60 * 60 * 24 * 365));
            header('Expires: '.gmdate(DATE_RFC1123,time()+ 60 * 60 * 24 * 365));
            header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime($filename)));
            header('Content-type: ' . $ntct[exif_imagetype($filename)]);
            header("Pragma: cache");

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                header('HTTP/1.1 304 Not Modified');
            }

            die(readfile($filename));
        }
    }

    /**
     * Resize an image based on $sourcePath and writes it back to $resizedPath.
     * 
     * @param mixed $sourcePath     The source image path to resize
     * @param mixed $resizedPath    The destination path to save the resized image in.
     * @param mixed $maxWidth       The maximum width of the resized image, defaults to 800.
     * @param mixed $maxHeight      The maximum height of the resized image, defaults to 2000.
     * @param mixed $imageQuality   The image quality used for the JPEG compression of the resized image, defaults to 70.
     * @return boolean              Whether or not resized succeeded.
     */
    private function resizeImage($sourcePath, $resizedPath, $maxWidth = 800, $maxHeight = 2000, $imageQuality = 70)
    {
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($sourcePath);

        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($sourcePath);
                break;

            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($sourcePath);
                break;

            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($sourcePath);
                break;
        }

        if ($source_gd_image === false)
        {
            return false;
        }

        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $maxWidth / $maxHeight;

        if ($source_image_width <= $maxWidth && $source_image_height <= $maxHeight)
        {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        }
        elseif ($thumbnail_aspect_ratio > $source_aspect_ratio)
        {
            $thumbnail_image_width = (int) ($maxHeight * $source_aspect_ratio);
            $thumbnail_image_height = $maxHeight;
        }
        else
        {
            $thumbnail_image_width = $maxWidth;
            $thumbnail_image_height = (int) ($maxWidth / $source_aspect_ratio);
        }

        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        imagejpeg($thumbnail_gd_image, $resizedPath, $imageQuality);
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);

        return true;
    }
}