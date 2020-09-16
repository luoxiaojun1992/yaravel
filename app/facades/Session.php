<?php

/**
 * Class Session
 *
 * @method static boolean set(string $name ,mixed $value)
 * @method static mixed get(string $name = NULL)
 * @method static boolean has(string $name)
 * @method static boolean del(string $name)
 */
class Session extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'services.session';
    }
}
