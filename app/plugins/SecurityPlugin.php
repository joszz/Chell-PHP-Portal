<?php
namespace Chell\Plugins;

use Phalcon\Events\Event;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Dispatcher;

/**
 * Handles security related tasks as a plugin within Phalcon.
 *
 * @package Plugins
 */
class SecurityPlugin extends Injectable
{
    private array $publiclyAccessible = [
        ['controller' => 'rss',         'actions' => ['*']],
        ['controller' => 'index',       'actions' => ['healthcheck', 'manifest', 'worker']],
        ['controller' => 'install',     'actions' => ['*']],
        ['controller' => 'speedtest',   'actions' => ['share']]
    ];

    /**
     * Called before executing each function. If not authenticated and requested controller is not rss or session,
     * then forward to session controller.
     *
     * @param Event $event                  The fired Phalcon Event.
     * @param Dispatcher $dispatcher        The Phalcon Dispatcher.
     * @return bool                         Whether or not to allow access to the current requested action.
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) : bool
    {
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        $config = $this->di->get('config');

        if (!$config->general->installed && $controller !== 'install')
        {
            $dispatcher->forward(['controller' => 'install', 'action' => 'index']);
            return false;
        }



        foreach ($this->publiclyAccessible AS $access)
        {
            if ($controller == $access['controller'] && (in_array('*', $access['actions']) || in_array($action, $access['actions'])))
            {
                return true;
            }
        }

        if (!$this->session->get('auth') && $controller != 'session')
        {
            $dispatcher->forward(['controller' => 'session', 'action' => 'index']);
            return false;
        }

        return true;
    }
}