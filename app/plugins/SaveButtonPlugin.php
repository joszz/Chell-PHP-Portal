<?php
namespace Chell\Plugins;

use Phalcon\Events\Event;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\View;

class SaveButtonPlugin extends Injectable
{

    public function beforeRender(Event $event, View $view) : bool
    {
        if (isset($view->form))
        {
            $view->setSaveButton = true;
        }

        return true;
    }
}