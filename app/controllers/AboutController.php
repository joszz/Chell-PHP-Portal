<?php

class AboutController extends BaseController
{
    public function indexAction()
    {
        $this->view->htmlClass = 'blackbg';
        $this->view->containerFullHeight = true;

        $url = "https://api.github.com/repos/joszz/Chell-PHP-Portal/stats/contributors";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        
        $content = current(json_decode(curl_exec($curl)));
        curl_close($curl);

        $this->view->versionMajor = 0;
        $this->view->versionMinor = 1;
        $this->view->versionCommit = $content->total; 
        $this->view->versionStability = 'alpha';
    }
}