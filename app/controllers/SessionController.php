<?php

use Phalcon\Http\Response;
use Phalcon\Http\Response\Cookies;

/**
 * The controller responsible for handling the session.
 *
 * @package Controllers
 */
class SessionController extends BaseController
{
    private $loginFailed = false;

    /**
     * Sets session cookie with user id and username.
     *
     * @param mixed $user   The user object to populate the session with.
     */
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

    /**
     * Override BaseController's initialize function so menu will not be retrieved/displayed.
     */
    public function initialize()
    {
        $this->config = $this->di->get('config');
    }

    /**
     * Shows the login form. Forwards to login action when rememberme cookies are present.
     */
    public function indexAction()
    {
        if(!$this->loginFailed && $this->cookies->has('username') && $this->cookies->has('password'))
        {
            return $this->dispatcher->forward(
                array(
                    'controller' => 'session',
                    'action'     => 'login'
                )
            );
        }

        $this->view->containerFullHeight = true;
        $this->view->form = new LoginForm($this->config, $this->loginFailed);
    }

    /**
     * Handles login with either POST variables or remember me cookie values.
     * If success redirects to dashboard (IndexController), unsuccesfull forward to index/loginform
     */
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

    /**
     * Logout user, destroying session and invalidating remember me cookie. Forwards to login form.
     */
    public function logoutAction(){
        $response = new Response();

        $response->setCookies($this->cookies->set('username', '', strtotime('-1 year')));
        $response->setCookies($this->cookies->set('password', '', strtotime('-1 year')));
        $this->session->destroy();

        return $response->redirect('session/index');
    }
}
