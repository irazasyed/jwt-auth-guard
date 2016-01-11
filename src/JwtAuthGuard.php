<?php

namespace Irazasyed\JwtAuthGuard;

use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JwtAuthGuard implements Guard
{
    use GuardHelpers;

    /**
     * @var string
     */
    protected $token = null;

    /**
     * The user we last attempted to retrieve.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $lastAttempted;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $this->parseToken();

        if (!JWTAuth::check()) {
            return null;
        }

        $id = JWTAuth::getPayload()->get('sub');

        return $this->user = $this->provider->retrieveById($id);
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function once(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return $this->attempt($credentials, false);
    }

    /**
     * Attempt to authenticate the user using the given credentials and return the token.
     *
     * @param array $credentials
     * @param bool  $login
     *
     * @return mixed
     */
    public function attempt(array $credentials = [], $login = true)
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            if ($login) {
                $this->setUser($user);

                return JWTAuth::fromUser($user);
            }

            return true;
        }

        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed $id
     *
     * @return bool
     */
    public function onceUsingId($id)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return true;
        }

        return false;
    }

    /**
     * Logout the user.
     *
     * @param bool $forceForever
     *
     * @return bool
     */
    public function logout($forceForever = true)
    {
        $this->user = null;
        $this->invalidate($forceForever);
        JWTAuth::unsetToken();
    }

    /**
     * Generate new token by ID.
     *
     * @param  mixed $id
     *
     * @return string|null
     */
    public function generateTokenById($id)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            return JWTAuth::fromUser($user);
        }

        return null;
    }

    /**
     * Refresh current expired token.
     *
     * @return string
     */
    public function refresh()
    {
        $this->parseToken();

        return JWTAuth::refresh();
    }

    /**
     * Invalidate current token (add it to the blacklist).
     *
     * @param  boolean $forceForever
     *
     * @return boolean
     */
    public function invalidate($forceForever = false)
    {
        $this->parseToken();

        return JWTAuth::invalidate($forceForever);
    }

    /**
     * Parse token from request if a token is not set.
     */
    protected function parseToken()
    {
        if (is_null($this->token)) {
            $this->checkForToken();

            JWTAuth::parseToken();
        }
    }

    /**
     * Get the token.
     *
     * @return false|Token
     */
    public function getToken()
    {
        return JWTAuth::getToken();
    }

    /**
     * Set the token.
     *
     * @param  Token|string $token
     *
     * @return JwtGuard
     */
    public function setToken($token)
    {
        $this->token = $token;

        JWTAuth::setToken($token);

        return $this;
    }

    /**
     * Get the raw Payload instance.
     *
     * @return \Tymon\JWTAuth\Payload
     */
    public function getPayload()
    {
        return JWTAuth::getPayload();
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed $user
     * @param  array $credentials
     *
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Get the last user we attempted to authenticate.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getLastAttempted()
    {
        return $this->lastAttempted;
    }

    /**
     * Return the currently cached user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the user provider used by the guard.
     *
     * @return \Illuminate\Contracts\Auth\UserProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set the user provider used by the guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     *
     * @return void
     */
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Check the request for the presence of a token
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function checkForToken()
    {
        if (!JWTAuth::parser()->setRequest($this->request)->hasToken()) {
            throw new BadRequestHttpException('Token not provided');
        }
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}