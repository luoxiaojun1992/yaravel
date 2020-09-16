<?php

/**
 * Class DB
 *
 * @method static void enableQueryLog()
 * @method static array getQueryLog()
 */
class DB extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
