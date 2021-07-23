<?php
namespace Chell\Plugins;

use Phalcon\Events\Event;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\View;

/**
 * Sets a variable indicating that a submit button is to be set in the navbar.
 *
 * @package Plugins
 */
class SaveButtonPlugin extends Injectable
{
    /**
     * Checks before rendering if a form variable is present in the view variables.
     * If so, add a save button in the header.
     *
     * @param Event $event  The beforeRender event
     * @param View $view    The view being rendered
     * @return bool         Success or failure.
     */
    public function beforeRender(Event $event, View $view) : bool
    {
        if (isset($view->form))
        {
            $view->setSaveButton = true;
        }

        return true;
    }
}