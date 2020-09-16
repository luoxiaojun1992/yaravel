<?php

namespace App\Support\Lock;

/**
 * Class Lock
 *
 * 注意事项：
 * 1. 分布式锁默认使用connection名为cache的redis连接，可以根据情况设置单独的连接
 * 2. 对于锁的一致性要求比较高的场景，可以考虑设置最高的redis持久化级别，或者在redis重启之前确保没有已经获得锁的任务在执行，避免并发执行
 *
 * @package App\Support\Lock
 */
class Lock
{
    /**
     * @return \Illuminate\Redis\Connections\Connection
     * @throws \Exception
     */
    protected static function getRedis()
    {
        $redisConnection = config('lock.redis_options.connection', 'cache');
        if (empty($redisConnection)) {
            throw new \Exception('empty lock redis connection');
        }
        return \RedisFacade::connection($redisConnection);
    }

    protected static function getKey($key)
    {
        return config('lock.redis_options.key_prefix', 'lock:') . $key;
    }

    public static function getLock($name, $ttl)
    {
        return new RedisLock(static::getRedis(), static::getKey($name), $ttl);
    }

    /**
     * 获取锁，同时执行callback
     *
     * {@inheritdoc}
     *
     * 如果成功获取到锁，
     * 如果callback可执行，返回callback执行结果，否则返回bool值
     * 如果获取锁失败，返回false
     *
     * @param $name
     * @param $ttl
     * @param $callback
     * @return mixed
     */
    public static function get($name, $ttl, $callback = null)
    {
        return static::getLock($name, $ttl)->get($callback);
    }

    /**
     * 释放锁
     *
     * @param $name
     */
    public static function release($name)
    {
        static::getLock($name, 0)->release();
    }

    /**
     * 续租
     *
     * @param $name
     * @param $ttl
     * @return int
     */
    public static function delay($name, $ttl)
    {
        return static::getLock($name, 0)->delay($ttl);
    }

    /**
     * 获取锁过期时间
     *
     * @param $name
     * @return int
     */
    public static function ttl($name)
    {
        return static::getLock($name, 0)->ttl();
    }
}
