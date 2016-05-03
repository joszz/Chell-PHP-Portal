<?php
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

class SecurityPlugin extends Plugin
{

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $controller = $dispatcher->getControllerName();
        
        if (!$this->session->get('auth') && $controller != 'session') {
            $dispatcher->forward(
                array(
                    'controller' => 'session',
                    'action'     => 'index'
                )
            );

            return false;
        } 
    }
}