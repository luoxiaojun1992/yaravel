<?php

namespace App\Services\Providers;

use App\Support\Arr;
use Illuminate\Redis\RedisManager;

class RedisServiceProvider extends AbstractLaravelProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        $this->app->singleton('redis', function ($app) {
            $config = $app->make('config')->get('database.redis');
            $redis = new RedisManager(Arr::get($config, 'client', 'predis'), $config);
            $this->registry->alias('services.redis', $redis);
            return $redis;
        });

        $this->app->alias('redis', \Illuminate\Redis\RedisManager::class);
        $this->app->alias('redis', \Illuminate\Contracts\Redis\Factory::class);

        $this->app->bind('redis.connection', function ($app) {
            return $app['redis']->connection();
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
            'redis', 'redis.connection', \Illuminate\Redis\RedisManager::class,
            \Illuminate\Contracts\Redis\Factory::class,
        ];
    }
}
