<?php

namespace Chell\Controllers;

use Chell\Models\Users;
use Chell\Forms\LoginForm;
use Duo\Web;

use Phalcon\Http\Response;

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
     * @param Users $user   The user object to populate the session with.
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
     * Shows the login form. Forwards to login action when remember me cookies are present.
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
     * If success redirects to dashboard (IndexController), unsuccessful forward to index/login form
     */
    public function loginAction()
    {
        $rememberMe = false;

        if ($this->request->isPost() && $this->config->application->debug)
        {
            $username = trim($this->request->getPost('username'));
            $password = trim($this->request->getPost('password'));
            $rememberMe = $this->request->getPost('rememberme');
        }
        else if($this->cookies->has('username') && $this->cookies->has('password'))
        {
            $username = trim($this->cookies->get('username')->getValue());
            $password = trim($this->cookies->get('password')->getValue());
        }

        $user = Users::findFirst(
            array(
                'username = :username:',
                'bind' => array(
                    'username' => $username,
                )
            )
        );

        if ($user && $this->security->checkHash($password, $user->password))
        {
            //Duo 2 factor login
            if($this->config->duo->enabled)
            {
                if($rememberMe)
                {
                    $this->cookies->set('username', $username, strtotime('+1 year'));
                    $this->cookies->set('password', $password, strtotime('+1 year'));
                }

                return $this->dispatcher->forward(
                    array(
                        'controller' => 'session',
                        'action'     => 'duo',
                        'params' => [$user]
                    )
                );
            }
            //Normal login
            else
            {
                $response = new Response();

                if($rememberMe)
                {
                    $response->setCookies($this->cookies->set('username', $username, strtotime('+1 year')));
                    $response->setCookies($this->cookies->set('password', $password, strtotime('+1 year')));
                }

                $this->_registerSession($user);
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();

                return $response->redirect('');
            }
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
     * Show the Dou iframe when 2 factor authentication has been enabled in config.
     * 
     * @param Users $user The user that tries to login.
     */
    public function duoAction($user)
    {
        $this->view->containerFullHeight = true;
        $this->view->signRequest = Web::signRequest($this->config->duo->ikey, $this->config->duo->skey, $this->config->duo->akey, $user->username);
    }

    /**
     * Callback method for the Duo iFrame. Check the user, set session, update the last login time and redirect to dashboard.
     */
    public function duoVerifyAction()
    {
        $username = Web::verifyResponse($this->config->duo->ikey, $this->config->duo->skey, $this->config->duo->akey, $_POST['sig_response']);

        $user = Users::findFirst(
            array(
                'username = :username:',
                'bind' => array(
                    'username' => $username,
                )
            )
        );

        if($user)
        {
            $this->_registerSession($user);
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();

            $response = new Response();
            return $response->redirect('');
        }
    }

    /**
     * Logout user, destroying session and invalidating remember me cookie. Forwards to login form.
     */
    public function logoutAction()
    {
        $response = new Response();

        $response->setCookies($this->cookies->set('username', '', strtotime('-1 year')));
        $response->setCookies($this->cookies->set('password', '', strtotime('-1 year')));
        $this->session->destroy();

        return $response->redirect('session/index');
    }
}
