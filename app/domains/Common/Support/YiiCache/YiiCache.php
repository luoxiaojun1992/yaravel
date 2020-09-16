<?php

namespace App\Domains\Common\Support\YiiCache;

/**
 * Class YiiCache
 *
 * {@inheritdoc}
 * 兼容yii1的cache，新cache不要使用
 *
 * @see \Predis\Client
 * @method static int hset(string $key, string $field, string $value)
 * @method static string hget(string $key, string $field)
 * @method static int hexists(string $key, string $field)
 * @package App\Utils\YiiCache
 */
class YiiCache
{
    protected static $connection = 'yii1_cache';

    protected static function getRedis($withoutPrefix = false)
    {
        $connectionName = $withoutPrefix ? static::$connection . '_without_prefix' : static::$connection;
        return \RedisFacade::connection($connectionName);
    }

    public static function set($key, $value, $ttl = 0)
    {
        //兼容yii1的cache dependency，这里dependency写死为null
        $value = serialize([$value, null]);
        if ($ttl > 0) {
            return static::getRedis()->set($key, $value, 'EX', $ttl);
        } else {
            return static::getRedis()->set($key, $value);
        }
    }

    public static function get($key)
    {
        $value = unserialize(static::getRedis()->get($key));
        //兼容yii1的cache dependency，这里dependency写死为null
        if (is_array($value) && isset($value[0])) {
            return $value[0];
        }

        return false;
    }

    public static function del($key)
    {
        return static::getRedis()->del([$key]);
    }

    public static function ttl($key)
    {
        return static::getRedis()->ttl($key);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::getRedis(true), $name], $arguments);
    }
}
