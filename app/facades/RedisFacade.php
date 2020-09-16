<?php

/**
 * Class RedisFacade
 *
 * @method static \Illuminate\Redis\Connections\Connection connection(string|null $name = null)
 */
class RedisFacade extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'redis';
    }
}
