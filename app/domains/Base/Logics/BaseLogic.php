<?php

namespace App\Domains\Base\Logics;

class BaseLogic
{
    public function __construct()
    {
        \Registry::set(static::class, $this);
    }
}
