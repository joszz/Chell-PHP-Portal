<?php

namespace Chell\Messages;

/**
 * Wrapper class for translations in Chell.
 *
 * @package Messages
 */
class TranslatorWrapper
{
    public $helpContent, $helpTitles, $validation;

    /**
     * Given the translationFile, set the translation messages.
     *
     * @param string $translationPath The translation file to use.
     */
    public function __construct($translationPath)
    {
        require_once((is_dir($translationPath) ? $translationPath :  APP_PATH . 'app/messages/en') . '/validation.php');

        $this->validation =  $validation;
    }
}