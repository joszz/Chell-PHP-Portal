<?php

namespace Chell\Exceptions;

/**
 * The custom exception for this project, used to handle errors caught by PHP's set_exception_handler.
 *
 * @package Exceptions
 */
class ChellException extends \Exception
{
    public $type;
    public $time;
    private $surroundingLines = 10;

    /**
     * Receives the original exception and sets the variables.
     *
     * @param \Throwable $exception     The original exception.
     */
    public function __construct(\Throwable $exception)
    {
        parent::__construct(ucfirst($exception->getMessage()), $exception->getCode(), $exception);

        $this->type = get_class($exception);
        $this->time = time();
        $this->line = $exception->getLine() - ($this->type == 'ParseError' ? 2 : 0);
        $this->file = $exception->getFile();
    }

    /**
     * Since it's not possible to override getTrace, redefine as getStacktrace.
     * return original exceptions trace.
     *
     * @return array    The original exceptions trace.
     */
    public function getStacktrace()
    {
        return $this->getPrevious()->getTrace();
    }

    /**
     * Retrieves the contents of the file the exception was generated in as an array.
     * Slice the array to have 10 lines before and after the exception. Then return as concatenated string.
     *
     * @return string   The partial contents of the file where the exception occurred.
     */
    private function getFileContents()
    {
        if (is_file($file = $this->getFile())) 
        {
            $fileContents = file($file);

            $exceptionContentsStart = $this->line - $this->surroundingLines >= 0 ? $this->line - $this->surroundingLines : 0;
            $exceptionFileContents = implode('', array_slice($fileContents, $exceptionContentsStart, $this->surroundingLines * 2));

            return htmlentities($exceptionFileContents);
        }

        return '';
    }

    /**
     * Retrieves the HTML for PrismJS.
     *
     * @return string PrismJS compatible HTML.
     */
    public function getFileHighlight()
    {
        return '<pre data-line="' . $this->surroundingLines . '"><code class="language-php">'. $this->getFileContents() . '</code></pre>';
    }

    /**
     * Retrieves the datetime the exception occurred.
     *
     * @param mixed $format     The format to use for PHP's date function. Defaults to 'd-m-Y H:i:s'.
     * @return string           The formatted date string the exception occurred.
     */
    public function getDate($format = 'd-m-Y H:i:s')
    {
        return date($format, $this->time);
    }
}