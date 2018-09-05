<?php

namespace Chell\Messages;

use Phalcon\Translate\Adapter\NativeArray;

/**
 * Wrapper class for translations in Chell.
 *
 * @package Messages
 */
class TranslatorWrapper
{
    public $helpContent, $helpTitles;

    /**
     * Given the translationFile, set the translation messages.
     * @param mixed $translationFile    The translation file to use.
     */
    public function __construct($translationFile)
    {
        require file_exists($translationFile) ? $translationFile :  APP_PATH . 'app/messages/en.php';

        $this->helpContent = new NativeArray([ "content" => $help ]);
        $this->helpTitles = new NativeArray([ "content" => $helpTitles ]);
    }
}