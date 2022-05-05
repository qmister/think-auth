<?php

namespace tp5er\think\auths\Encrypt;

/**
 * Interface EncryptInterface
 * @package tp5er\think\auths\Encrypt
 */
interface EncryptInterface
{
    /**
     * @param  array  $payload
     *
     * @return string
     */
    public function encode(array $payload);

    /**
     * @param  string  $token
     *
     * @return array
     */
    public function decode($token);
}