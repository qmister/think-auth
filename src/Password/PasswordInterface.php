<?php

namespace tp5er\think\auths\Password;

use tp5er\think\auths\Contracts\Authenticatable;



interface PasswordInterface
{
    /**
     * 加密
     * @param string $password
     * @return string
     */
    public function encrypt(string $password);

    /**
     * 验证
     * @param Authenticatable $user
     * @param string $password
     * @return bool
     */
    public function verify(Authenticatable $user, string $password);
}