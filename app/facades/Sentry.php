<?php

/**
 * Class Sentry
 *
 * @method static string|null captureMessage(string $message, array $params = array(), array $data = array(), bool|array $stack = false, mixed $vars = null)
 * @method static string|null captureException(\Throwable|\Exception $exception, array $data = null, mixed $logger = null, mixed $vars = null)
 */
class Sentry extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \App\Services\Sentry\SentryServiceProvider::$abstract;
    }
}
