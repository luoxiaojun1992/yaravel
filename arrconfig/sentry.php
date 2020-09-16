<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => boolval(intval(env('SENTRY_BREADCRUMBS_SQL_BINDINGS', true))),

    // Capture default user context
    'user_context' => boolval(intval(env('SENTRY_USER_CONTEXT', false))),

//    'transport' => [\App\Services\Sentry\Transports\RedisTransport::class, 'handle'],

    'redis_options' => [
        'queue_name' => env('SENTRY_REDIS_QUEUE_NAME', 'queue:sentry:transport'),
        'connection' => env('SENTRY_REDIS_CONNECTION', 'sentry'),
    ],
];
