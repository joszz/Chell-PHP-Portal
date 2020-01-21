<?php

namespace Chell\Messages;

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

        $this->helpContent =  $help ;
        $this->helpTitles =  $helpTitles;
    }
}