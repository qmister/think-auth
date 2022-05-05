<?php

namespace tp5er\think\auths\Exceptions;

use Exception;

/**
 * Class JWTException
 * @package tp5er\think\auths\Exceptions
 */
class JWTException extends Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'An error occurred';
}