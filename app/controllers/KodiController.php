<?php
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

        $this->view->bgImage = '../getImage/movies/fanart/' . $movie->idMovie;
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

        $this->view->episode = $episode;
    }

    /**
     * Show ablum details.
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
    public function getImageAction($which, $type, $id)
    {
        switch($which)
        {
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

        if(isset($url))
        {
            $ntct = Array('1' => 'image/gif',
                          '2' => 'image/jpeg',
                          '3' => 'image/png',
                          '6' => 'image/bmp');
            $filename = getcwd() . '/img/cache/' . basename($url);

            if(!file_exists($filename))
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                file_put_contents($filename, $output);
            }

            session_cache_limiter('none');
            header('Cache-control: max-age='.(60 * 60 * 24 * 365));
            header('Expires: '.gmdate(DATE_RFC1123,time()+ 60 * 60 * 24 * 365));
            header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime($filename)));
            header('Content-type: ' . $ntct[exif_imagetype($filename)]);
            header("Pragma: cache");

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
            {
                header('HTTP/1.1 304 Not Modified');
            }

            die(readfile($filename));
        }
    }
}