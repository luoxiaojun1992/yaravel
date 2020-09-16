<?php

/**
 * class Log.
 *
 * @method static void emergency(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static \Illuminate\Log\Writer channel(string $channelName)
 * @method static bool hasChannel(string $channelName)
 * @method static \App\Services\Log\Logger[] channels()
 */
class Log extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'log_manager';
    }
}
