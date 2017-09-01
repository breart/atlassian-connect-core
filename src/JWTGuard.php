<?php

namespace AtlassianConnectCore;

use AtlassianConnectCore\Helpers\JWTHelper;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Auth\Recaller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * Class JWTGuard
 *
 * @package App\Providers\Guards
 */
class JWTGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The name of the token "column" in persistent storage.
     *
     * @var string
     */
    protected $storageKey = 'client_key';

    /**
     * The session used by the guard.
     *
     * @var \Illuminate\Contracts\Session\Session
     */
    protected $session;

    /**
     * The Illuminate cookie creator service.
     *
     * @var \Illuminate\Contracts\Cookie\QueueingFactory
     */
    protected $cookie;

    /**
     * Indicates if a token user retrieval has been attempted.
     *
     * @var bool
     */
    protected $recallAttempted = false;

    /**
     * Create a new authentication guard.
     *
     * @param UserProvider $provider
     * @param Session $session
     * @param CookieJar $cookie
     */
    public function __construct(UserProvider $provider, Session $session, CookieJar $cookie)
    {
        $this->provider = $provider;
        $this->session = $session;
        $this->cookie = $cookie;
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
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        if ($token = request('jwt', request()->header('Authorization'))) {
            $token = last(explode(' ', $token));

            $user = $this->provider->retrieveByCredentials(
                [$this->storageKey => $this->getTokenKey($token)]
            );
        }
        else {

            $id = $this->session->get($this->getName());

            // First we will try to load the user using the identifier in the session if
            // one exists. Otherwise we will check for a "remember me" cookie in this
            // request, and if one exists, attempt to retrieve the user using that.
            $user = null;

            if (! is_null($id)) {
                $user = $this->provider->retrieveById($id);
            }

            // If the user is null, but we decrypt a "recaller" cookie we can attempt to
            // pull the user data on that cookie which serves as a remember cookie on
            // the application. Once we have a user we can return it to the caller.
            $recaller = $this->recaller();

            if (is_null($user) && ! is_null($recaller)) {
                $user = $this->userFromRecaller($recaller);

                if ($user) {
                    $this->updateSession($user->getAuthIdentifier());
                }
            }
        }

        return $this->user = $user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials['token'])) {
            return false;
        }

        $key = $this->getTokenKey($credentials['token']);
        $credentials = [$this->storageKey => $key];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
    }

    /**
     * Get JWT token key
     *
     * @param string $token
     *
     * @return mixed
     */
    protected function getTokenKey($token)
    {
        $decoded = JWTHelper::decode($token);

        return array_get($decoded, 'body.iss');
    }

    /**
     * Attempt user authentication
     *
     * @return bool
     */
    public function attempt()
    {
        if(!$this->check()) {
            return false;
        }

        $user = $this->user();

        // Update user session items
        $this->updateSession($user->getAuthIdentifier());

        $this->ensureRememberTokenIsSet($user);

        $this->queueRecallerCookie($user);

        return true;
    }

    /**
     * Update the session with the given ID.
     *
     * @param  string  $id
     * @return void
     */
    protected function updateSession($id)
    {
        $this->session->put($this->getName(), $id);

        $this->session->migrate(true);
    }

    /**
     * Get a unique identifier for the auth session value.
     *
     * @return string
     */
    public function getName()
    {
        return 'jwt_session_' . sha1(static::class);
    }

    /**
     * Refresh the "remember me" token for the user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    protected function cycleRememberToken(AuthenticatableContract $user)
    {
        $user->setRememberToken($token = Str::random(60));

        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * Create a new "remember me" token for the user if one doesn't already exist.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    protected function ensureRememberTokenIsSet(AuthenticatableContract $user)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * Queue the recaller cookie into the cookie jar.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    protected function queueRecallerCookie(AuthenticatableContract $user)
    {
        $this->getCookieJar()->queue($this->createRecaller(
            $user->getAuthIdentifier().'|'.$user->getRememberToken()
        ));
    }

    /**
     * Create a "remember me" cookie for a given ID.
     *
     * @param  string  $value
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function createRecaller($value)
    {
        return $this->getCookieJar()->forever($this->getRecallerName(), $value);
    }

    /**
     * Get the cookie creator instance used by the guard.
     *
     * @return \Illuminate\Contracts\Cookie\QueueingFactory
     *
     * @throws \RuntimeException
     */
    public function getCookieJar()
    {
        if (! isset($this->cookie)) {
            throw new \RuntimeException('Cookie jar has not been set.');
        }

        return $this->cookie;
    }

    /**
     * Set the cookie creator instance used by the guard.
     *
     * @param  \Illuminate\Contracts\Cookie\QueueingFactory  $cookie
     * @return void
     */
    public function setCookieJar(CookieJar $cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember_session_' . sha1(static::class);
    }

    /**
     * Get the decrypted recaller cookie for the request.
     *
     * @return \Illuminate\Auth\Recaller|null
     */
    protected function recaller()
    {
        if (is_null($this->request)) {
            return null;
        }

        if ($recaller = $this->request->cookies->get($this->getRecallerName())) {
            return new Recaller($recaller);
        }
    }

    /**
     * Pull a user from the repository by its "remember me" cookie token.
     *
     * @param  \Illuminate\Auth\Recaller  $recaller
     * @return mixed
     */
    protected function userFromRecaller($recaller)
    {
        if (! $recaller->valid() || $this->recallAttempted) {
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
}