<?php

namespace Chell\Controllers;

/**
 * @todo finish this
 * @package Controllers
 */
class ErrorController
{
    private $exception;
    private $content;
    private $debug = false;
    private $css = array('prism.css'), $js = array('prism.js');

    public function __construct($exception)
    {
        $this->exception = $exception;
        $this->debug = ini_get('display_errors') == 'on';
        $this->css[] = 'compressed/' . scandir(getcwd() . '/css/compressed/')[2];

        ob_start();
        if ($this->debug) {
            $this->exception();
        }
        else {
            $this->error();
        }
        $this->content = ob_get_clean();

        require_once(APP_PATH . 'app/views/layouts/exception.phtml');
    }

	private function exception()
	{
        require_once(APP_PATH . 'app/views/error/exception.phtml');
	}

	private function error()
	{
        require_once(APP_PATH . 'app/views/error/error.phtml');
	}
}