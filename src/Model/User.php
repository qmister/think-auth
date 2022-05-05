<?php

namespace tp5er\think\auths\Model;

use think\Model;
use tp5er\think\auths\Contracts\Authenticatable as AuthenticatableContract;


class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
}