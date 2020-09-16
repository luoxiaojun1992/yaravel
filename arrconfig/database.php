<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', 'jing_'),
            'strict' => boolval(intval(env('DB_STRICT', true))),
            'engine' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'), //phpredis supports persistent connection and key prefix

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
            'persistent' => (bool)(int)env('REDIS_PERSISTENT', 0),
            'timeout' => doubleval(env('REDIS_TIMEOUT', 5)),
            'read_timeout' => doubleval(env('REDIS_READ_TIMEOUT', 5)),
        ],

        'cache' => [
            'host' => env('REDIS_CACHE_HOST', '127.0.0.1'),
            'password' => env('REDIS_CACHE_PASSWORD', null),
            'port' => env('REDIS_CACHE_PORT', 6379),
            'database' => env('REDIS_CACHE_DATABASE', 1),
            'prefix' => env('REDIS_CACHE_PREFIX', 'LOCAL_'),
            'persistent' => (bool)(int)env('REDIS_CACHE_PERSISTENT', 0),
            'timeout' => doubleval(env('REDIS_CACHE_TIMEOUT', 5)),
            'read_timeout' => doubleval(env('REDIS_CACHE_READ_TIMEOUT', 5)),
        ],

        'queue' => [
            'host' => env('REDIS_QUEUE_HOST', '127.0.0.1'),
            'password' => env('REDIS_QUEUE_PASSWORD', null),
            'port' => env('REDIS_QUEUE_PORT', 6379),
            'database' => env('REDIS_QUEUE_DATABASE', 2),
            'persistent' => (bool)(int)env('REDIS_QUEUE_PERSISTENT', 0),
            'prefix' => env('REDIS_QUEUE_PREFIX', 'LOCAL_'),
            'timeout' => doubleval(env('REDIS_TIMEOUT', 5)),
            'read_timeout' => doubleval(env('REDIS_READ_TIMEOUT', 5)),
        ],

        'sentry' => [
            'host' => env('REDIS_SENTRY_HOST', '127.0.0.1'),
            'password' => env('REDIS_SENTRY_PASSWORD', null),
            'port' => env('REDIS_SENTRY_PORT', 6379),
            'database' => env('REDIS_SENTRY_DATABASE', 3),
            'persistent' => (bool)(int)env('REDIS_SENTRY_PERSISTENT', 0),
            'timeout' => doubleval(env('REDIS_SENTRY_TIMEOUT', 1)),
            'read_timeout' => doubleval(env('REDIS_SENTRY_READ_TIMEOUT', 1)),
        ],

        'zipkin' => [
            'host' => env('REDIS_ZIPKIN_HOST', '127.0.0.1'),
            'password' => env('REDIS_ZIPKIN_PASSWORD', null),
            'port' => env('REDIS_ZIPKIN_PORT', 6379),
            'database' => env('REDIS_ZIPKIN_DATABASE', 4),
            'persistent' => (bool)(int)env('REDIS_ZIPKIN_PERSISTENT', 0),
            'timeout' => doubleval(env('REDIS_ZIPKIN_TIMEOUT', 1)),
            'read_timeout' => doubleval(env('REDIS_ZIPKIN_READ_TIMEOUT', 1)),
        ],
    ],

];
