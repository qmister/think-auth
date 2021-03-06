<?php

namespace tp5er\think\auth\Guards;

use think\Cookie;
use think\helper\Str;
use think\Session;
use tp5er\think\auth\Contracts\Authenticatable;
use tp5er\think\auth\Support\Recaller;

/**
 * Class SessionGuard
 * @package tp5er\think\auth\Guards
 */
class SessionGuard extends Guard
{

    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Cookie
     */
    protected $cookie;
    /**
     * @var Authenticatable|null
     */
    protected $lastAttempted;

    /**
     * @var bool
     */
    protected $loggedOut = false;
    /**
     * Indicates if a token user retrieval has been attempted.
     *
     * @var bool
     */
    protected $recallAttempted = false;

    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;


    protected function initialize()
    {
        $this->session = $this->app->session;
        $this->cookie  = $this->app->cookie;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        if ($this->loggedOut) {
            return;
        }
        return $this->user()
            ? $this->user()->getAuthIdentifier()
            : $this->session->get($this->getName());
    }

    /**
     * Set the current user.
     *
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user      = $user;
        $this->loggedOut = false;
        $this->fireAuthenticatedEvent($user);
        return $this;
    }


    /**
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->fireAttemptEvent($credentials, $remember);
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);
            return true;
        }
        $this->fireFailedEvent($user, $credentials);
        return false;
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        $this->fireAttemptEvent($credentials);
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);
            return true;
        }
        return false;
    }

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     * @param bool $remember
     * @return Authenticatable|false
     */
    public function loginUsingId($id, $remember = false)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }
        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param mixed $id
     * @return Authenticatable|false
     */
    public function onceUsingId($id)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);
            return $user;
        }
        return false;
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return $this->viaRemember;
    }

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user
     * @param bool $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());
        // ???????????????????????????????????????????????????????????????
        // ????????????????????????????????????cookie
        // ???????????? ???????????????????????????????????????????????????
        if ($remember) {
            $this->ensureRememberTokenIsSet($user);

            $this->queueRecallerCookie($user);
        }
        // ???????????????????????????????????????????????????????????????????????????????????????
        // ???????????????????????????????????????????????????????????????
        // ??????????????????????????????????????????????????????
        $this->fireLoginEvent($user, $remember);
        $this->setUser($user);
    }


    /**
     * Get the currently authenticated user.
     * @return Authenticatable|null
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }
        // ???????????????????????????????????????????????????????????????
        // ??????????????? ??????????????????????????????
        // ????????????????????????????????????????????????
        if (!is_null($this->user)) {
            return $this->user;
        }
        $id = $this->session->get($this->getName());
        if (!is_null($id) && $this->user = $this->provider->retrieveById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }
        // ????????????????????????????????????????????????????????????cookie?????????????????????
        // ????????? cookie ????????????????????????????????? cookie
        // ??????????????? ?????????????????????????????????????????????????????????????????????
        if (is_null($this->user) && !is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);
            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());
                $this->fireLoginEvent($this->user, true);
            }
        }
        return $this->user;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();
        $this->clearUserDataFromStorage();

        if (!is_null($this->user) && !empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
        // ??????????????????????????????????????????????????????????????????
        // ??????????????????????????????????????????????????????
        // ????????????????????????????????????????????????

        $this->currentDeviceLogout($user);

        $this->user      = null;
        $this->loggedOut = true;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        return $this->hasValidCredentials($user, $credentials);

    }

    /**
     * Remove the user data from the session and cookies.
     *
     * @return void
     */
    protected function clearUserDataFromStorage()
    {
        $this->session->delete($this->getName());
    }

    /**
     * Pull a user from the repository by its "remember me" cookie token.
     *
     * @param Recaller $recaller
     * @return mixed
     */
    protected function userFromRecaller($recaller)
    {
        if (!$recaller->valid() || $this->recallAttempted) {
            return;
        }
        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $this->recallAttempted = true;

        $this->viaRemember = !is_null($user = $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token()
        ));

        return $user;
    }

    /**
     * Get the decrypted recaller cookie for the request.
     *
     * @return Recaller|null
     */
    protected function recaller()
    {
        if (is_null($this->request)) {
            return;
        }
        if ($recaller = $this->cookie->get($this->getRecallerName())) {
            return new Recaller($recaller);
        }
    }


    /**
     * Queue the recaller cookie into the cookie jar.
     *
     * @param Authenticatable $user
     * @return void
     */
    protected function queueRecallerCookie(Authenticatable $user)
    {
        $value = $user->getAuthIdentifier() . '|' . $user->getRememberToken() . '|' . $user->getAuthPassword();
        $this->cookie->forever($this->getRecallerName(), $value);
    }

    /**
     * Create a new "remember me" token for the user if one doesn't already exist.
     *
     * @param Authenticatable $user
     * @return void
     */
    protected function ensureRememberTokenIsSet(Authenticatable $user)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * Refresh the "remember me" token for the user.
     *
     * @param Authenticatable $user
     * @return void
     */
    protected function cycleRememberToken(Authenticatable $user)
    {
        $user->setRememberToken($token = Str::random(60));
        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * Update the session with the given ID.
     *
     * @param string $id
     * @return void
     */
    protected function updateSession($id)
    {
        $this->session->set($this->getName(), $id);
        $this->session->regenerate(true);
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param mixed $user
     * @param array $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        $validated = !is_null($user) && $this->provider->validateCredentials($user, $credentials);
        if ($validated) {
            $this->fireValidatedEvent($user);
        }
        return $validated;
    }


    /**
     * Get a unique identifier for the auth session value.
     *
     * @return string
     */
    public function getName()
    {
        return 'login_' . $this->name . '_' . sha1(static::class);
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember_' . $this->name . '_' . sha1(static::class);
    }
}