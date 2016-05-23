<?php

class KodiController extends BaseController
{
    public function initialize()
    {
        $this->view->setMainView('layouts/empty');
    }

    public function movieAction($id)
    {
        $movie = KodiMovies::findFirst(array(
            'conditions' => 'idMovie = ?1',
            'bind'       => array(1 => $id),
        ));
        $movie = current(KodiMovies::extractMovieImagesFromXML(array($movie)));
        
        $movie->trailer = substr($movie->c19, $start = strpos($movie->c19, 'videoid=') + 8);
        $movie->rating = floor($movie->c05 * 2) / 2;
        //$movie->filePath = str_replace('/', '\\', str_replace('smb://', '', $movie->c22)) . $movie->getFile()->strFilename;
        
        $this->view->movie = $movie;
    }

    public function episodeAction($id)
    {
        $episode = KodiTVShowEpisodes::findFirst(array(
            'conditions' => 'idEpisode = ?1',
            'bind'       => array(1 => $id),
        ));
        $episode->rating = floor($episode->c03 * 2) / 2;
        
        $this->view->episode = $episode;
    }

    public function albumAction($id)
    {
        $album = KodiMusic::findFirst(array(
            'conditions' => 'idAlbum = ?1',
            'bind'       => array(1 => $id),
        ));
        
        $this->view->album = $album;
    }

    public function getImageAction()
    {
        switch($_GET['which'])
        {
            case 'movies':
                $item = KodiMovies::findFirst(array(
                    'conditions' => 'idMovie = ?1',
                    'bind'       => array(1 => $_GET['id']),
                ));
                $url = current(KodiMovies::extractMovieImagesFromXML(array($item)))->c08;
                break;

            case 'albums':
                $item = KodiMusic::findFirst(array(
                    'conditions' => 'idAlbum = ?1',
                    'bind'       => array(1 => $_GET['id']),
                ));
                $url = current(KodiMusic::extractAlbumImagesFromXML(array($item)))->strImage;
                break;

            case 'episodes':
                $item = KodiTVShowEpisodes::findFirst(array(
                    'conditions' => 'idEpisode = ?1',
                    'bind'       => array(1 => $_GET['id']),
                ));
                $url = current(KodiTVShowEpisodes::extractMovieImagesFromXML(array($item)))->c06;
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