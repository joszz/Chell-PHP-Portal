<?php
namespace Chell\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

/**
 * Handles security related tasks as a plugin within Phalcon.
 *
 * @package Plugins
 */
class SecurityPlugin extends Plugin
{
    private $publiclyAccessible = array(
        ['controller' => 'rss', 'action' => '*'],
        ['controller' => 'index', 'action' => 'manifest'],
        ['controller' => 'speedtest', 'action' => 'share']
    );
    /**
     * Called before executing each function. If not authenticated and requested controller is not rss or session,
     * then forward to session controller.
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        foreach($this->publiclyAccessible AS $access) 
        {
            if($controller == $access['controller'] && ($access['action'] == '*'  || $action == $access['action'])) 
            {
                return true;
            }
        }

        if (!$this->session->get('auth') && $controller != 'session')
        {
            $dispatcher->forward(array('controller' => 'session', 'action' => 'index'));
            return false;
        }

        return true;
    }
}