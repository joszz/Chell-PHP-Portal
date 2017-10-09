<?php

namespace Chell\Controllers;

use Phalcon\Debug\Dump;
use Chell\Exceptions\ChellException;

/**
 * The controller responsible for handling all errors.
 *
 * @package Controllers
 */
class ErrorController
{
    private $exception;
    private $content;
    private $config;
    private $css = array('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
    private $js = array('https://code.jquery.com/jquery-3.2.1.min.js');
    private $logPath = APP_PATH . 'app/logs/';

    public $logFile;
    public $debug = false;

    /**
     * Controller created by FrontController, bypassing most of the Phalcon framework to have less of a dependency.
     * The constructor handles rendering and logging of the error page.
     *
     * @param ChellException $exception     The exception being thrown.
     */
    public function __construct(ChellException $exception, $config)
    {
        ob_clean();

        $this->config = $config;
        $this->exception = $exception;
        $this->debug = ini_get('display_errors') == 'on';
        $this->setLogFile();

        $this->css[] = $this->config->application->baseUri . 'css/prism.css';
        $this->js[] = $this->config->application->baseUri . 'js/prism.js';

        $this->content = $this->exception();
        $exceptionContent = $this->layout();

        if ($this->debug) {
            $this->content = $exceptionContent;
        }
        else {
            $this->content = $this->error();
            $this->content = $this->layout();
        }

        $this->writeLogAsHTML($exceptionContent);

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        die($this->content);
    }

    /**
     * Wrapper for Phalcon Debug.
     *
     * @param mixed $dump   The variable to dump.
     * @return string       The dumped variable as string.
     */
    public function dump($dump)
    {
        return (new Dump())->variable($dump);
    }

    /**
     * This will render the exceptions view when display_errors flag is on in php.ini.
     *
     * @return string   The rendered HTML as a string
     */
    private function exception()
    {
        ob_start();
        require_once(APP_PATH . 'app/views/error/exception.phtml');
        return ob_get_clean();
    }

    /**
     * This will show a user friendly error, not revealing details.
     *
     * @return string   The rendered HTML as a string
     */
    private function error()
    {
        ob_start();
        require_once(APP_PATH . 'app/views/error/error.phtml');
        return ob_get_clean();
    }

    /**
     * This will render the layout for exception and error views.
     *
     * @return string   The rendered HTML as a string
     */
    private function layout()
    {
        ob_start();
        require(APP_PATH . 'app/views/layouts/exception.phtml');
        return ob_get_clean();
    }

    /**
     * Sets the log filename based on datetime.
     */
    private function setLogFile()
    {
        $filename = $this->getGUID() . '.htm';

        while (is_file($this->logPath . $filename)) {
            $filename = $this->getGUID() . '.htm';
        }

        $this->logFile = $filename;
    }

    /**
     * Generates a unique string.
     *
     * @return string   A GUID string.
     */
    private function getGUID()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * This will write the rendered HTML in $this->content to the logs folder GZipped.
     */
    private function writeLogAsHTML($content)
    {
        $gzip = gzopen($filePath = $this->logPath . $this->logFile, 'w9');
        gzwrite($gzip, $content);
        gzclose($gzip);
    }
}