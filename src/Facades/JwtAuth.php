<?php

namespace tp5er\think\auths\Facades;

use think\Facade;
use tp5er\think\auths\Contracts\Authenticatable;
use tp5er\think\auths\Guards\Guard;
use tp5er\think\auths\JwtAuthManager;
use tp5er\think\auths\Support\Payload;

/**
 * Class JwtAuth
 * @package tp5er\think\auths\Facades
 * @method static JwtAuthManager setAuth($name = null)
 * @method static Guard auth()
 * @method static JwtAuthManager setToken($token)
 * @method static Payload getPayload()
 * @method static string attempt(array $credentials = [])
 * @method static Authenticatable|null authenticate()
 * @method static string refresh()
 * @method static int id()
 * @method static string getRequestToken()
 *
 */
class JwtAuth extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'auth.jwt';
    }
}