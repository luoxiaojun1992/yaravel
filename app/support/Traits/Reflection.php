<?php

namespace App\Support\Traits;

trait Reflection
{
    protected function isInstantiableClass($className)
    {
        return \App\Support\Reflection::isInstantiableClass($className);
    }
}
