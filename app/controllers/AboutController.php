<?php
/**
 * The controller showing about information of this project.
 *
 * @package Controllers
 */
class AboutController extends BaseController
{
    /**
     * Show version information, using CURL to retrieve commit count.
     */
    public function indexAction()
    {
        $this->view->containerFullHeight = true;
        $content = $this->getGithubStats();

        $this->view->versionMajor = 0;
        $this->view->versionMinor = 1;
        $this->view->versionCommit = $content->total;
        $this->view->versionStability = '&alpha;';

        $this->view->lastUpdatedPHPDoc = date('d-m-Y H:i:s', filemtime(getcwd() . '/documentation/phpdoc/'));
        $this->view->lastUpdatedYUIDoc = date('d-m-Y H:i:s', filemtime(getcwd() . '/documentation/yuidoc/'));
    }

    private function getGithubStats(){
        $curl = curl_init("https://api.github.com/repos/joszz/Chell-PHP-Portal/stats/contributors");
        curl_setopt_array ($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
        ));

        $content = current(json_decode(curl_exec($curl)));
        curl_close($curl);

        if (empty($content->total)) {
            return $this->getGithubStats();
        }

        return $content;
    }
}