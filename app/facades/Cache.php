<?php

/**
 * Class Cache
 *
 * @method static bool has(string $key)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static mixed pull(string $key, mixed $default = null)
 * @method static void put(string $key, mixed $value, \DateTimeInterface|\DateInterval|float|int $minutes)
 * @method static bool add(string $key, mixed $value, \DateTimeInterface|\DateInterval|float|int $minutes)
 * @method static int|bool increment(string $key, mixed $value = 1)
 * @method static int|bool decrement(string $key, mixed $value = 1)
 * @method static void forever(string $key, mixed $value)
 * @method static mixed remember(string $key, \DateTimeInterface|\DateInterval|float|int $minutes, \Closure $callback)
 * @method static bool forget(string $key)
 * @method static mixed driver(string $driver = null)
 */
class Cache extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}
