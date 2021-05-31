<?php

namespace Chell\Controllers;

use Chell\Models\Users;
use Chell\Forms\LoginForm;
use Davidearl\WebAuthn\WebAuthn;
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
     * Initializes the controller, adding JS being used.
     */
    public function initialize()
    {
        parent::initialize();

        if (DEBUG)
        {
            $this->assets->collection('login')->addJs('js/login.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
            $this->assets->collection('login')->addJs('vendor/webauthn/webauthnauthenticate.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
        }
        else
        {
            $this->assets->collection('login')->addJs('js/login.min.js', true, false, ['defer' => 'defer'], $this->settings->application->version, true);
        }
    }

    /**
     * Sets session cookie with user id and username.
     *
     * @param \Phalcon\Mvc\ModelInterface $user The user object to populate the session with.
     */
    private function _registerSession($user)
    {
        $this->session->set(
            'auth',
            [
                'id'        => $user->id,
                'username'  => $user->username
            ]
        );
    }

    /**
     * Shows the login form. Forwards to login action when remember me cookies are present.
     */
    public function indexAction()
    {
        if (!$this->loginFailed && $this->cookies->has('username') && $this->cookies->has('password'))
        {
            return $this->dispatcher->forward([
                'controller' => 'session',
                'action'     => 'login'
            ]);
        }

        $this->view->containerFullHeight = true;
        $this->view->form = new LoginForm($this->loginFailed);
    }

    /**
     * Handles login with either POST variables or remember me cookie values.
     * If success redirects to dashboard (IndexController), unsuccessful forward to index/login form
     *
     * @return mixed    Either forward to duoAction when duo is enabled in config, or redirects back to homepage
     */
    public function loginAction()
    {
        $rememberMe = false;
        $username = '';
        $password = '';

        if ($this->request->isPost() && $this->security->checkToken())
        {
            $username = trim($this->request->get('username'));
            $password = trim($this->request->get('password'));
            $rememberMe = $this->request->get('rememberme');
        }
        else if ($this->cookies->has('username') && $this->cookies->has('password'))
        {
            $username = trim($this->cookies->get('username')->getValue());
            $password = trim($this->cookies->get('password')->getValue());
        }

        $user = Users::findFirst([
            'username = :username:',
            'bind' => ['username' => $username]
        ]);

        if ($user && $this->security->checkHash($password, $user->password))
        {
            //Duo 2 factor login
            if ($this->settings->duo->enabled)
            {
                if ($rememberMe)
                {
                    $this->cookies->set('username', $username, strtotime('+1 year'), $this->settings->application->base_uri, true);
                    $this->cookies->set('password', $password, strtotime('+1 year'), $this->settings->application->base_uri, true);
                }

                return $this->dispatcher->forward([
                    'controller' => 'session',
                    'action'     => 'duo',
                    'params'     => [$user]
                ]);
            }
            //Normal login
            else
            {
                $response = new Response();

                if ($rememberMe)
                {
                    $response->setCookies($this->cookies->set('username', $username, strtotime('+1 year', $this->settings->application->base_uri, true)));
                    $response->setCookies($this->cookies->set('password', $password, strtotime('+1 year', $this->settings->application->base_uri, true)));
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

        $this->dispatcher->forward([
            'controller' => 'session',
            'action'     => 'index'
        ]);
    }

    /**
     * Creates the challenge for the webauthentication. Called through AJAX in login.js
     */
    public function webauthchallengeAction()
    {
        if ($this->request->isPost())
        {
            $user = Users::findFirst([
                'username = :username:',
                'bind' => ['username' => $this->request->get('username')]
            ]);

            $webauthn = new WebAuthn($_SERVER['HTTP_HOST']);

            die(json_encode($webauthn->prepareForLogin($user->webauthn)));
        }
    }

    /**
     * Authenticates WebAuthN authentication requests.
     *
     * @return mixed    Either forward to duoAction when duo is enabled in config, or redirects back to homepage
     */
    public function webauthauthenticateAction()
    {
        if ($this->request->isPost())
        {
            $user = Users::findFirst([
                'username = :username:',
                'bind' => ['username' => $this->request->get('username')]
            ]);
            $webauthn = new WebAuthn($_SERVER['HTTP_HOST']);

            if ($webauthn->authenticate($this->request->get('webauth'), $user->webauthn))
            {
                if ($this->settings->duo->enabled)
                {
                    return $this->dispatcher->forward([
                        'controller' => 'session',
                        'action'     => 'duo',
                        'params'     => [$user]
                    ]);
                }
                else
                {
                    $this->_registerSession($user);
                    $user->last_login = date('Y-m-d H:i:s');
                    $user->save();

                    $response = new Response();
                    return $response->redirect('');
                }
            }
        }

        $this->loginFailed = true;
        $this->dispatcher->forward([
            'controller' => 'session',
            'action'     => 'index'
        ]);
    }

    /**
     * Show the Dou iframe when 2 factor authentication has been enabled in config.
     *
     * @param Users $user The user that tries to login.
     */
    public function duoAction($user)
    {
        $this->view->containerFullHeight = true;
        $this->view->dnsPrefetchRecords = ['https://' . $this->settings->duo->api_hostname];
        $this->view->signRequest = Web::signRequest($this->settings->duo->ikey, $this->settings->duo->skey, $this->settings->duo->akey, $user->username);
    }

    /**
     * Callback method for the Duo iFrame. Check the user, set session, update the last login time and redirect to dashboard.
     */
    public function duoVerifyAction() : \Phalcon\Http\ResponseInterface
    {
        $username = Web::verifyResponse($this->settings->duo->ikey, $this->settings->duo->skey, $this->settings->duo->akey, $_POST['sig_response']);

        $user = Users::findFirst([
            'username = :username:',
            'bind' => ['username' => $username]
        ]);

        if ($user)
        {
            if ($user instanceof Users)
            {
                $this->_registerSession($user);
            }
            else
            {
                throw new \Exception('Expected type Users, got type' . get_class($user));
            }

            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
        }

        $response = new Response();
        return $response->redirect('/');
    }

    /**
     * Logout user, destroying session and invalidating remember me cookie. Forwards to login form.
     */
    public function logoutAction()
    {
        $this->cookies->set('username', 'username', strtotime('-1 year'), $this->settings->application->base_uri, true);
        $this->cookies->set('password', 'password', strtotime('-1 year'), $this->settings->application->base_uri, true);
        $this->session->destroy();

        $this->view->containerFullHeight = true;
        $this->view->form = new LoginForm($this->loginFailed);
        $this->view->pick('session/index');
    }
}
