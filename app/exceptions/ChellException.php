<?php

namespace Chell\Exceptions;

/**
 * The custom exception for this project, used to handle errors caught by PHP's set_error_handler.
 *
 * @package Exceptions
 */
class ChellException extends \Exception
{
    public $type;
    private $surroundingLines = 10;

    public function __construct(\Throwable $exception)
    {
        parent::__construct(ucfirst($exception->getMessage()), $exception->getCode(), $exception);

        $this->type = get_class($exception);
        $this->line = $exception->getLine() - ($this->type == 'ParseError' ? 2 : 0);
        $this->file = $exception->getFile();
    }

    private function getFileContents()
    {
        if (is_file($file = $this->getFile())) {
            $fileContents = file($file);

            $exceptionContentsStart = $this->line - $this->surroundingLines >= 0 ? $this->line - $this->surroundingLines : 0;
            $exceptionFileContents = array_slice($fileContents, $exceptionContentsStart, $this->surroundingLines * 2);
            $exceptionFileContents = trim(implode('', $exceptionFileContents));

            return $exceptionFileContents;
        }

        return '';
    }

    public function getFileHighlight()
    {
        return '<pre data-line="' . $this->surroundingLines . '"><code class="language-php">'. $this->getFileContents() . '</code></pre>';
    }
}