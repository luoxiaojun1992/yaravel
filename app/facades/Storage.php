<?php

/**
 * Class Storage
 *
 * @method static \Illuminate\Contracts\Filesystem\Filesystem disk(string $name = null)
 */
class Storage extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}
