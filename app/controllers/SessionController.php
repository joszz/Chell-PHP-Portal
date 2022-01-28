<?php

namespace Chell\Controllers;

use DateTime;
use Chell\Models\Users;
use Chell\Forms\LoginForm;
use Davidearl\WebAuthn\WebAuthn;
use Duo\DuoUniversal\Client as DuoClient;
use Duo\DuoUniversal\DuoException;
use Phalcon\Mvc\ModelInterface;

/**
 * The controller responsible for handling the session.
 *
 * @package Controllers
 */
class SessionController extends BaseController
{
    private bool $loginFailed = false;
    private string $loginMessage = '';

    /**
     * Initializes the controller, adding JS being used.
     */
    public function initialize()
    {
        parent::initialize();

        $this->assets->addScripts(['login', 'webauthnauthenticate', 'toggle-passwords']);
        $this->assets->addStyleAndScript('bootstrap-toggle');
        $this->view->loginMessage = $this->loginMessage;
    }

    /**
     * Sets session cookie with user id and username.
     *
     * @param ModelInterface $user The user object to populate the session with.
     */
    private function _registerSession(ModelInterface $user)
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

        if ($user)
        {
            $now = new DateTime();
            $lastFailedLogin = new DateTime($user->last_failed_attempt);

            if ($user->failed_logins + 1 >= 5 && $now->diff($lastFailedLogin)->i < 5)
            {
                $this->loginMessage = 'User locked out for 5 minutes!';
            }
            else if ($this->security->checkHash($password, $user->password))
            {
                //Duo 2 factor login
                if ($this->settings->duo->enabled)
                {
                    if ($rememberMe)
                    {
                        $this->cookies->set('username', $username, strtotime('+1 year'), BASEPATH, true);
                        $this->cookies->set('password', $password, strtotime('+1 year'), BASEPATH, true);
                    }

                    $client = $this->getDuoClient();
                    $user->duostate = $client->generateState();
                    $user->save();

                    return $this->response->redirect($client->createAuthUrl($user->username, $user->duostate));
                }
                //Normal login
                else
                {
                    if ($rememberMe)
                    {
                        $this->response->setCookies($this->cookies->set('username', $username, strtotime('+1 year', BASEPATH, true)));
                        $this->response->setCookies($this->cookies->set('password', $password, strtotime('+1 year', BASEPATH, true)));
                    }

                    $this->_registerSession($user);
                    $user->last_login = $now->format('Y-m-d H:i:s');
                    $user->last_failed_attempt = null;
                    $user->failed_logins = 0;
                    $user->save();

                    return $this->response->redirect('');
                }
            }
            else
            {
                $user->failed_logins = $now->diff($lastFailedLogin)->i >= 5 ? 1 : $user->failed_logins + 1;
                $user->last_failed_attempt = $now->format('Y-m-d H:i:s');
                $user->save();
                $this->loginFailed = true;
            }
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
        $this->view->disable();

        if ($this->request->isPost())
        {
            $user = Users::findFirst([
                'username = :username:',
                'bind' => ['username' => $this->request->get('username')]
            ]);

            $webauthn = new WebAuthn($_SERVER['HTTP_HOST']);

            $this->response->setJsonContent($webauthn->prepareForLogin($user->webauthn))->send();
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

                    return $this->response->redirect('');
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
     * Callback method for the Duo iFrame. Check the user, set session, update the last login time and redirect to dashboard.
     */
    public function duoVerifyAction()
    {
        $username = trim($this->cookies->get('username')->getValue());
        $user = Users::findFirst([
            'username = :username:',
            'bind' => ['username' => $username]
        ]);

        if ($user)
        {
            $client = $this->getDuoClient();
            $state = $this->request->getQuery("state");
            $duo_code = $this->request->getQuery("duo_code");

            if (empty($user->duostate) || $state !== $user->duostate) {
                return $this->dispatcher->forward([
                    'controller' => 'session',
                    'action'     => 'logout'
                ]);
            }

            try {
                $decoded_token = $client->exchangeAuthorizationCodeFor2FAResult($duo_code, $username);
            }
            catch (DuoException $e) {
                return $this->dispatcher->forward([
                    'controller' => 'session',
                    'action'     => 'logout'
                ]);
            }

            $this->_registerSession($user);
            $this->assets->addScript('duo');

            $user->failed_logins = 0;
            $user->last_failed_attempt = $user->duostate = null;
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
        }
    }

    /**
     * Logout user, destroying session and invalidating remember me cookie. Forwards to login form.
     */
    public function logoutAction()
    {
        $this->assets->addStyleAndScript('bootstrap-toggle');
        $this->cookies->set('username', 'username', strtotime('-1 year'), BASEPATH, true);
        $this->cookies->set('password', 'password', strtotime('-1 year'), BASEPATH, true);
        $this->session->destroy();

        $this->view->containerFullHeight = true;
        $this->view->form = new LoginForm($this->loginFailed);
        $this->view->pick('session/index');
    }

    private function getDuoClient() : DuoClient
    {
        return new DuoClient(
            $this->settings->duo->clientid,
            $this->settings->duo->clientsecret,
            $this->settings->duo->api_hostname,
            'https://' . $_SERVER['SERVER_NAME'] . $this->url->get('session/duoVerify')
        );
    }
}
