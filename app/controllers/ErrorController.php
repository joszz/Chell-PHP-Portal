<?php

namespace Chell\Controllers;

use Phalcon\Debug\Dump;

use Chell\Exceptions\ChellException;

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

    public $dump;

    public function __construct(ChellException $exception)
    {
        $this->exception = $exception;
        $this->dump = new Dump();
        $this->debug = ini_get('display_errors') == 'on';
        $this->css[] = 'compressed/' . scandir(APP_PATH . 'public/css/compressed/')[2];


        ob_start();
        if ($this->debug) {
            $this->exception();
        }
        else {
            $this->error();
        }
        $this->content = ob_get_clean();

        ob_start();
        require_once(APP_PATH . 'app/views/layouts/exception.phtml');
        $this->content = ob_get_clean();

        $this->writeLogAsHTML();

        die($this->content);
    }

    public function dump($dump)
    {
        return (new Dump())->variable($dump);
    }

	private function exception()
	{
        require_once(APP_PATH . 'app/views/error/exception.phtml');
	}

	private function error()
	{
        require_once(APP_PATH . 'app/views/error/error.phtml');
	}

    private function writeLogAsHTML()
    {
        $filename = date('Y-m-d[H-i-s]') . '.htm';
        $path = APP_PATH . 'app/logs/';

        $i = 0;
        while (is_file($path . $filename)) {
            $filename = date('Y-m-d[H-i-s]')  .'-' . ++$i . '.htm';
        }

        file_put_contents($path . $filename, $this->content);
    }
}