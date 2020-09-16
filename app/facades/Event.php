<?php

/**
 * Class Event
 *
 * @method static array|null fire(string|object $event, mixed $payload = [], bool $halt = false)
 * @method static void listen(string|array $events, mixed $listener)
 */
class Event extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'events';
    }
}
