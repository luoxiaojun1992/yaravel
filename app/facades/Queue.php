<?php

/**
 * Class Queue
 *
 * @method static \Illuminate\Contracts\Queue\Queue connection(string $name = null)
 * @method static mixed push(string|object $job, mixed $data = '', string $queue = null);
 * @method static mixed pushRaw(string $payload, string $queue = null, array $options = [])
 * @method static mixed later(\DateTimeInterface|\DateInterval|int $delay, string|object $job, mixed $data = '', string $queue = null);
 * @method static \Illuminate\Contracts\Queue\Job|null pop(string $queue = null)
 */
class Queue extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'queue';
    }
}
