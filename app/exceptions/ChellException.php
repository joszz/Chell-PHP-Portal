<?php

namespace Chell\Exceptions;

/**
 * The custom exception for this project, used to handle errors caught by PHP's set_error_handler.
 *
 * @package Exceptions
 */
class ChellException extends Exception
{
    public function __construct($message, $code = 0, $line, $file, Exception $previous = null) 
    {
        parent::__construct($message, $code, $previous);

        $this->line = $line;
        $this->file = $file;
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}