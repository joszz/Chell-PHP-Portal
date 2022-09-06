<?php
namespace Chell\Plugins;

use Exception;
use Phalcon\Logger\Logger;

/**
 * Handles all JS/CSS assets for the application.
 *
 * @package Plugins
 */
class ChellLogger extends Logger
{
    public function LogException(Exception $exception)
    {
        $this->critical($exception->getMessage());
        $this->debug('File: ' . $exception->getFile() . PHP_EOL . 'Line: ' . $exception->getLine() . PHP_EOL . 'Stacktrace:' . $exception->getTraceAsString());
    }
}