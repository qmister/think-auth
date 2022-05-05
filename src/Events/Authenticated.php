<?php

namespace tp5er\think\auths\Events;

use tp5er\think\auths\Contracts\Authenticatable;

/**
 * Class Authenticated
 * @package tp5er\think\auths\Events
 */
class Authenticated
{
    /**
     * The authentication guard name.
     *
     * @var string
     */
    public $guard;

    /**
     * The authenticated user.
     *
     * @var Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param string $guard
     * @param Authenticatable $user
     * @return void
     */
    public function __construct($guard, $user)
    {
        $this->user  = $user;
        $this->guard = $guard;
    }
}