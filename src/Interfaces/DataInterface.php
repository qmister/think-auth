<?php

namespace tp5er\think\auths\Interfaces;

/**
 * Interface DataInterface
 * @package tp5er\think\auths\Interfaces
 */
interface DataInterface
{
    /**
     * @return array
     */
    public function toArray();

    /**
     * @return string
     */
    public function toJson();
}