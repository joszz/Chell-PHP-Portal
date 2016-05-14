<?php
/**
 * The controller responsible for all dashboard related actions.
 * 
 * @package Chell\Controllers
 */
class IndexController extends BaseController
{
    /**
     * Shows the dashboard view
     * 
     * @return  The dashboard view
     */
    public function indexAction()
    {
        $this->view->menu = Menus::findFirst(array(
            'conditions' => 'id = ?1',
            'order'      => 'name',
            'bind'       => array(1 => 1),
        ));

        $this->view->disks = Diskdrives::DiskStatisticsLocal();
        $this->view->devices = Devices::find(array('order' => 'name ASC'));
        $this->view->movies = KodiMovies::getLatestMovies();
        $this->view->albums = KodiMusic::getLatestAlbums();
        $this->view->episodes = KodiTVShowEpisodes::getLatestEpisodes();
    }

    public function getImageAction()
    {
        if(isset($_GET['url']))
        {
            $ntct = Array('1' => 'image/gif',
                          '2' => 'image/jpeg',
                          '3' => 'image/png',
                          '6' => 'image/bmp');
            $filename = getcwd() . '/img/cache/' . basename($_GET['url']);

            if(!file_exists($filename))
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $_GET['url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                file_put_contents($filename, $output);
            }

            header('Content-type: ' . $ntct[exif_imagetype($filename)]);
            die(readfile($filename));
        }
    }
}