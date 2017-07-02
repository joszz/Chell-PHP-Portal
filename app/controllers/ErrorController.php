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
    private $css = array('css/prism.css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
    private $js = array('js/prism.js', 'https://code.jquery.com/jquery-3.2.1.min.js');
    private $logPath = APP_PATH . 'app/logs/';

    public $logFile;
    public $debug = false;

    /**
     * Controller created by FrontController, bypassing most of the Phalcon framework to have less of a dependency.
     * The constructor handles rendering and logging of the error page.
     *
     * @param ChellException $exception     The exception being thrown.
     */
    public function __construct(ChellException $exception)
    {
        $this->exception = $exception;
        $this->debug = ini_get('display_errors') == 'on';
        $this->setLogFile();

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
	 */
	private function exception()
	{
        require_once(APP_PATH . 'app/views/error/exception.phtml');
	}

	/**
	 * This will show a user friendly error, not revealing details.
	 */
	private function error()
	{
        require_once(APP_PATH . 'app/views/error/error.phtml');
	}

    /**
     * Sets the log filename based on datetime.
     */
    private function setLogFile()
    {
        $filename = date('Y-m-d[H-i-s]') . '.htm';
        $i = 0;

        while (is_file($this->logPath . $filename)) {
            $filename = date('Y-m-d[H-i-s]')  .'-' . ++$i . '.htm';
        }

        $this->logFile = $filename;
    }

    /**
     * This will write the rendered HTML in $this->content to the logs folder.
     */
    private function writeLogAsHTML()
    {
        file_put_contents($this->logPath . $this->logFile, $this->content);
    }
}