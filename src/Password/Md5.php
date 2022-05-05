<?php

namespace tp5er\think\auths\Password;

use tp5er\think\auths\Contracts\Authenticatable;


class Md5 implements PasswordInterface
{
    /**
     * @param Authenticatable $user
     * @param string $password
     * @return bool
     */
    public function verify(Authenticatable $user, string $password)
    {
        return md5($password) === $user->getAuthPassword();
    }

    /**
     * 加密
     * @param string $password
     * @return string
     */
    public function encrypt(string $password)
    {
        return md5($password);
    }
}