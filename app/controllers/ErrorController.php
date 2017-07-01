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
    private $isDebug = false;

    public function __construct($exception)
    {
        $this->exception = $exception;
        $this->debug = ini_get('display_errors') == 'on';
        $this->css = scandir(getcwd() . '/css/compressed/')[2];

        ob_start();
        $this->exception();
        $this->content = ob_get_clean();

        require_once(APP_PATH . 'app/views/layouts/exception.phtml');
    }

	private function exception()
	{
        require_once(APP_PATH . 'app/views/error/exception.phtml');
	}
}