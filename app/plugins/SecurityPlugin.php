<?php
namespace Chell\Plugins;

use Phalcon\Events\Event;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Http\Request;
use Phalcon\Url;

/**
 * Handles security related tasks as a plugin within Phalcon.
 *
 * @package Plugins
 */
class SecurityPlugin extends Injectable
{
    private array $publiclyAccessible = [
        ['controller' => 'rss', 'action' => '*'],
        ['controller' => 'index', 'action' => 'manifest'],
        ['controller' => 'index', 'action' => 'worker'],
        ['controller' => 'install', 'action' => '*'],
        ['controller' => 'speedtest', 'action' => 'share']
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

        foreach ($this->publiclyAccessible AS $access)
        {
            if ($controller == $access['controller'] && ($access['action'] == '*' || $action == $access['action']))
            {
                return true;
            }
        }

        if (!$this->session->get('auth') && $controller != 'session')
        {
            $url = str_replace(BASEPATH, '', (new Request())->getURI());
            $this->session->set('auth_redirect_url', $url);
            $dispatcher->forward(['controller' => 'session', 'action' => 'index']);
            return false;
        }

        return true;
    }
}