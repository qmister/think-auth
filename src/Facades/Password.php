<?php

namespace tp5er\think\auths\Facades;

use think\Facade;
use tp5er\think\auths\Contracts\Authenticatable;

/**
 * Class Password
 * @package tp5er\think\auths\Facades
 * @method static string encrypt(string $password)
 * @method static bool verify(Authenticatable $user, string $password)
 */
class Password extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'auth.password';
    }
}