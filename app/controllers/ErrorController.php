<?php

namespace Chell\Controllers;

use Chell\Exceptions\ChellException;
use Phalcon\Support\Debug\Dump;

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

    /**
     * Controller created by FrontController, bypassing most of the Phalcon framework to have less of a dependency.
     * The constructor handles rendering and logging of the error page.
     *
     * @param ChellException $exception The exception being thrown.
     * @param object $config            The config object with all the values of config.ini.
     */
    public function initialize(ChellException $exception)
    {
        if (ob_get_length()){
            ob_end_clean();
        }

        $this->exception = $exception;
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
     * Retrieves the User IP from the request headers.
     * @return string   The user's IP.
     */
    public function getUserIP() : string
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
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
            BASEPATH . 'css/exception.min.css',
            BASEPATH . 'css/prism-line-numbers.css'
        ];
        $this->js[] = BASEPATH . 'js/jquery.min.js';
        $this->js[] = BASEPATH . 'js/prism.min.js';
        $this->js[] = BASEPATH . 'js/prism-markup-templating.min.js';
        $this->js[] = BASEPATH . 'js/prism-line-numbers.min.js';
        $this->js[] = BASEPATH . 'js/prism-php-extras.min.js';
        $this->js[] = BASEPATH . 'js/prism-php.min.js';
        $this->js[] = BASEPATH . 'js/exception.min.js';

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
}