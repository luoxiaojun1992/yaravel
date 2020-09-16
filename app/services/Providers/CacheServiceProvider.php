<?php

namespace App\Services\Providers;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Factory;

class CacheServiceProvider extends AbstractLaravelProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cache', function ($app) {
            $cache = new CacheManager($app);
            $this->registry->alias('services.cache', $cache);
            return $cache;
        });

        $this->app->alias('cache', CacheManager::class);
        $this->app->alias('cache', Factory::class);

        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });

        $this->app->alias('cache.store', Repository::class);
        $this->app->alias('cache.store', \Illuminate\Contracts\Cache\Repository::class);

        $this->app->singleton('memcached.connector', function () {
            return new MemcachedConnector;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'cache', 'cache.store', 'memcached.connector',
            CacheManager::class, Factory::class,
            Repository::class, \Illuminate\Contracts\Cache\Repository::class,
        ];
    }
}
