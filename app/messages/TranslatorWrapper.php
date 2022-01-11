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
        $file = $translationPath . '/validation.php';
        if (!is_file($file))
        {
            $file = APP_PATH . 'app/messages/en//validation.php';
        }
        require_once($file);

        $this->validation =  $validation;
    }
}