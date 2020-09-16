<?php

namespace App\Support;

class Reflection
{
    public static function isInstantiableClass($className)
    {
        if (!class_exists($className)) {
            return false;
        }

        $reflectionClass = new \ReflectionClass($className);
        return $reflectionClass->isInstantiable();
    }
}
