<?php

use Phalcon\Http\Response;
use Phalcon\Http\Response\Cookies;

class SessionController extends BaseController
{
    private $loginFailed = false;

    private function _registerSession($user)
    {
        $this->session->set(
            'auth',
            array(
                'id'        => $user->id,
                'username'  => $user->username
            )
        );
    }

    public function indexAction()
    {
        $this->view->htmlClass = 'blackbg';
        $this->view->containerFullHeight = true;
        $this->view->form = new LoginForm($this->config);
        
        if(!$this->loginFailed && $this->cookies->has('username') && $this->cookies->has('password'))
        {
            return $this->dispatcher->forward(
                array(
                    'controller' => 'session',
                    'action'     => 'login'
                )
            );
        }
    }

    public function loginAction()
    {
        $rememberMe = false;

        if ($this->request->isPost())
        {
            $username = trim($this->request->getPost('username'));
            $password = trim($this->request->getPost('password'));
            $rememberMe = $this->request->getPost('rememberme');
        }
        else if( $this->cookies->has('username') && $this->cookies->has('password'))
        {
            $username = trim($this->cookies->get('username')->getValue());
            $password = trim($this->cookies->get('password')->getValue());
        }

        $user = Users::findFirst(
            array(
                "username = :username:",
                'bind' => array(
                    'username' => $username,
                )
            )
        );
        
        if ($user && $this->security->checkHash($password, $user->password))
        {
            $this->_registerSession($user);
            $response = new Response();

            if($rememberMe)
            {
                $response->setCookies($this->cookies->set('username', $username, strtotime('+1 year')));
                $response->setCookies($this->cookies->set('password', $password, strtotime('+1 year')));
            }
            
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();

            return $response->redirect('');
        }
        else 
        {
            $this->loginFailed = true;
        }    

        return $this->dispatcher->forward(
            array(
                'controller' => 'session',
                'action'     => 'index'
            )
        );
    }

    public function logoutAction(){
        $response = new Response();

        $response->setCookies($this->cookies->set('username', '', strtotime('-1 year')));
        $response->setCookies($this->cookies->set('password', '', strtotime('-1 year')));
        $this->session->destroy();

        return $response->redirect('session/index');
    }
}
