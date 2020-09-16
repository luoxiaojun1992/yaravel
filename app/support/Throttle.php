<?php

namespace App\Support;

use Illuminate\Cache\RateLimiter;

/**
 * Class Throttle
 *
 * 注意事项：
 * 1. 默认使用driver名为throttle的redis类型cache组件(redis connection默认使用cache)，可以根据情况设置单独的连接
 *
 * @package App\Support
 */
class Throttle
{
    /**
     * @return mixed
     * @throws \Exception
     */
    protected static function getCache()
    {
        $cacheDriver = config('throttle.cache.driver', 'throttle');
        if (empty($cacheDriver)) {
            throw new \Exception('empty throttle cache driver');
        }
        return \Cache::driver($cacheDriver);
    }

    protected static function getKey($key)
    {
        return config('throttle.cache.key_prefix', 'throttle:') . $key;
    }

    /**
     * @return RateLimiter
     * @throws \Exception
     */
    public static function getRateLimiter()
    {
        return new RateLimiter(static::getCache());
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  float|int  $decayMinutes
     * @return bool
     * @throws \Exception
     */
    public static function tooManyAttempts($key, $maxAttempts, $decayMinutes = 1)
    {
        $key = static::getKey($key);
        return static::getRateLimiter()->tooManyAttempts($key, $maxAttempts, $decayMinutes);
    }

    /**
     * Increment the counter for a given key for a given decay time.
     *
     * @param  string  $key
     * @param  float|int  $decayMinutes
     * @return int
     * @throws \Exception
     */
    public static function hit($key, $decayMinutes = 1)
    {
        $key = static::getKey($key);
        return static::getRateLimiter()->hit($key, $decayMinutes);
    }

    /**
     * Get the number of attempts for the given key.
     *
     * @param  string  $key
     * @return mixed
     * @throws \Exception
     */
    public static function attempts($key)
    {
        $key = static::getKey($key);
        return static::getRateLimiter()->attempts($key);
    }

    /**
     * Reset the number of attempts for the given key.
     *
     * @param  string  $key
     * @return mixed
     * @throws \Exception
     */
    public static function resetAttempts($key)
    {
        $key = static::getKey($key);
        return static::getRateLimiter()->resetAttempts($key);
    }

    /**
     * Get the number of retries left for the given key.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return int
     * @throws \Exception
     */
    public static function retriesLeft($key, $maxAttempts)
    {
        $key = static::getKey($key);
        return static::getRateLimiter()->retriesLeft($key, $maxAttempts);
    }

    /**
     * Clear the hits and lockout timer for the given key.
     *
     * @param  string  $key
     * @return void
     * @throws \Exception
     */
    public static function clear($key)
    {
        $key = static::getKey($key);
        static::getRateLimiter()->clear($key);
    }

    /**
     * Get the number of seconds until the "key" is accessible again.
     *
     * @param  string  $key
     * @return int
     * @throws \Exception
     */
    public static function availableIn($key)
    {
        $key = static::getKey($key);
        return static::getRateLimiter()->availableIn($key);
    }

    /**
     * @param string $key
     * @param int $maxAttempts
     * @param float|int $decayMinutes
     * @return bool
     * @throws \Exception
     */
    public static function pass($key, $maxAttempts, $decayMinutes = 1) {
        $rateLimiter = static::getRateLimiter();
        $key = static::getKey($key);
        if ($rateLimiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            return false;
        }

        return $rateLimiter->hit($key, $decayMinutes) <= $maxAttempts;
    }
}
