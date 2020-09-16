<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Yaf Skeleton'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'user_locale' => boolval(intval(env('APP_USER_LOCALE', false))),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => boolval(intval(env('APP_DEBUG', false))),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'PRC'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [
        \App\Services\Exceptions\ExceptionHandlerProvider::class,
        \App\Services\Log\LogServiceProvider::class,
        \App\Services\Sentry\SentryServiceProvider::class, //必须放在LogServiceProvider后面
        \App\Services\Providers\EventServiceProvider::class,
        \App\Services\Providers\BusServiceProvider::class,
        \App\Services\Providers\PipelineServiceProvider::class,
        \App\Services\Providers\DatabaseServiceProvider::class,
        \App\Services\Providers\RedisServiceProvider::class,
        \App\Services\Queue\QueueServiceProvider::class, //必须放在ExceptionHandlerProvider后面
        \App\Services\Providers\FilesystemServiceProvider::class,
        \App\Services\Providers\TranslationServiceProvider::class,
        \App\Services\Providers\ValidationServiceProvider::class,
        \App\Services\Providers\SessionProvider::class,
        \App\Services\Elasticsearch\ElasticSearchProvider::class,
        \App\Services\Zipkin\ZipkinServiceProvider::class,
        \App\Services\Providers\ViewServiceProvider::class,
        \App\Services\Providers\PaginationServiceProvider::class,
        \App\Services\Providers\CacheServiceProvider::class,
        \App\Services\Providers\CarbonServiceProvider::class,
        \App\Services\Mail\MailServiceProvider::class,
        \App\Services\Providers\EventsProvider::class,
    ],
];
