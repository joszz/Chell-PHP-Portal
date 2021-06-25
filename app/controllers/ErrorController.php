<?php

namespace Chell\Controllers;

use Chell\Exceptions\ChellException;
use Phalcon\Debug\Dump;

/**
 * The controller responsible for handling all errors.
 *
 * @package Controllers
 */
class ErrorController
{
    private ChellException $exception;
    private string $content;
    private array $css = [];
    private array $js = [];
    private string $logPath = APP_PATH . 'app/logs/';
    private string $logFile;

    public string $guid;

    /**
     * Controller created by FrontController, bypassing most of the Phalcon framework to have less of a dependency.
     * The constructor handles rendering and logging of the error page.
     *
     * @param ChellException $exception The exception being thrown.
     * @param object $config            The config object with all the values of config.ini.
     */
    public function __construct(ChellException $exception)
    {
        ob_clean();

        $this->exception = $exception;
        $this->guid = $this->getGUID();
        $this->setLogFile();

        $this->content = $this->exception();
        $exceptionContent = $this->layout();

        if (DEBUG)
        {
            $this->content = $exceptionContent;
        }
        else
        {
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
    public function dump($dump) : string
    {
        return (new Dump([
			"pre" => "",
			"arr" => "",
			"bool" => "",
			"float" => "",
			"int" => "",
			"null" => "",
			"num" => "",
			"obj" => "",
			"other" => "",
			"res" => "",
			"str" => "",
		]))->variable($dump);
    }

    /**
     * This will render the exceptions view when display_errors flag is on in php.ini.
     *
     * @return string   The rendered HTML as a string
     */
    private function exception() : string
    {
        $this->css = [
            'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css',
            BASEPATH . 'dist/css/exception.min.css'
        ];
        $this->js = [
            BASEPATH . 'dist/js/exception.min.js'
        ];

        ob_start();
        require_once(APP_PATH . 'app/views/error/exception.phtml');
        return ob_get_clean();
    }

    /**
     * This will show a user friendly error, not revealing details.
     *
     * @return string The rendered HTML as a string
     */
    private function error() : string
    {
        ob_start();
        require_once APP_PATH . 'app/views/error/error.phtml';
        return ob_get_clean();
    }

    /**
     * This will render the layout for exception and error views.
     *
     * @return string   The rendered HTML as a string
     */
    private function layout() : string
    {
        ob_start();
        require APP_PATH . 'app/views/layouts/exception.phtml';
        return ob_get_clean();
    }

    /**
     * Sets the log filename based on datetime.
     */
    private function setLogFile()
    {
        $filename = $this->guid . '.htm';

        while (is_file($this->logPath . $filename))
        {
            $this->guid = $this->getGUID();
            $filename = $this->guid . '.htm';
        }

        $this->logFile = $filename;
    }

    /**
     * Generates a unique string.
     *
     * @return string   A GUID string.
     */
    private function getGUID() : string
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * This will write the rendered HTML in $this->content to the logs folder GZipped.
     */
    private function writeLogAsHTML(string $content)
    {
        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0770);
        }

        $gzip = gzopen($this->logPath . $this->logFile, 'w9');
        gzwrite($gzip, $content);
        gzclose($gzip);
    }
}