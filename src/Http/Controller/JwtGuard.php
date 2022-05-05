<?php

namespace tp5er\think\auths\Http\Controller;

use tp5er\think\auths\JwtAuthManager;
use tp5er\think\auths\Traits\RequestToken;
use tp5er\think\auths\Traits\ResponseData;

/**
 * Trait Jwt
 * @package tp5er\think\auths\Http\Controller
 */
trait JwtGuard
{
    /**
     * @return JwtAuthManager
     */
    protected function jwt()
    {
        return app()->get('auth.jwt')->setAuth('web');
    }
}