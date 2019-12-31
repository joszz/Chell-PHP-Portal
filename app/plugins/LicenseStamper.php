<?php

namespace Chell\Plugins;

use Phalcon\Assets\FilterInterface;

/**
 * Adds a license message to the top of the file
 *
 * @param string $contents
 * @return string
 */
class LicenseStamper implements FilterInterface
{
    /**
     * Do the filtering
     *
     * @param string $contents
     * @return string
     */
    public function sanitize($contents, $sanitizers, $noRecursive)
    {
        $license = '/* Copyright (c) 2015 - ' . date('Y') . ' Jos Nienhuis */';

        return $license . PHP_EOL . trim($contents);
    }
}