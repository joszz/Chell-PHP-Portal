<?php

namespace Chell\Controllers;

/**
 * @todo finish this
 * @package Controllers
 */
class ErrorController
{
    private $exception;

    public function __construct($exception)
    {
        $this->exception = $exception;

        ob_start();
        if(get_class($exception) == 'PHPError') {
            $content = $this->phpException();
        }
        else {
            $content = $this->exception();
        }

        require_once(getcwd() . '/../app/views/layouts/exception.phtml');
        die(ob_get_clean());
    }

	private function phpException()
	{
        require_once(getcwd() . '/../app/views/error/php-exception.phtml');
	}

	private function exception()
	{
        require_once(getcwd() . '/../app/views/error/exception.phtml');
	}
}