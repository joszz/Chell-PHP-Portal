<?php

use Phalcon\Http\Response;
use Phalcon\Http\Response\Cookies;

class SessionController extends BaseController
{
    private function _registerSession($user)
    {
        $this->session->set(
            'auth',
            array(
                'id'   => $user->id,
                'username' => $user->username
            )
        );
    }

    public function indexAction()
    {
        $this->view->containerFullHeight = true;
        $this->view->form = new LoginForm();
        
        if( $this->cookies->has('username') && $this->cookies->has('password'))
        {
            return $this->dispatcher->forward(
                array(
                    'controller' => 'session',
                    'action'     => 'login'
                )
            );
        }
    }

    }
}
