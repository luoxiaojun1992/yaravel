<?php

/**
 * Class Zipkin
 *
 * @method static mixed serverSpan(string $name, callable $callback, bool $flush = false)
 * @method static mixed clientSpan(string $name, callable $callback, bool $flush = false)
 * @method static mixed span(string $name, callable $callback, null|string $kind = null, bool $flush = false)
 */
class Zipkin extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'services.zipkin';
    }
}
