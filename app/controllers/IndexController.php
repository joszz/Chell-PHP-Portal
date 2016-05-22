<?php
/**
 * The controller responsible for all dashboard related actions.
 * 
 * @package Chell\Controllers
 */
class IndexController extends BaseController
{
    private $executionTime;

    /**
     * Shows the dashboard view
     * 
     * @return  The dashboard view
     * @todo break up function
     */
    public function indexAction()
    {
        $this->view->menu = Menus::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => 1),
        ));

        $this->view->devices = Devices::find(array('order' => 'name ASC'));
        $this->view->movies = KodiMovies::getLatestMovies();
        $this->view->albums = KodiMusic::getLatestAlbums();
        $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
        
        $this->executionTime = -microtime(true);
        $this->view->phpsysinfoData = PHPSysInfo::getData();
        $this->view->PHPSysinfoExecutionTime = round(($this->executionTime + microtime(true)) * 1000, 2) . '&micro;s';
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