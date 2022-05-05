<?php

namespace tp5er\think\auths\Events;

use tp5er\think\auths\Contracts\Authenticatable;

/**
 * Class Registered
 * @package tp5er\think\auths\Events
 */
class Registered
{
    /**
     * The authenticated user.
     *
     * @var Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param Authenticatable  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}