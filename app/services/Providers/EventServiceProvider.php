<?php

namespace App\Services\Providers;

use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Events\Dispatcher;

class EventServiceProvider extends AbstractLaravelProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('events', function ($app) {
            $event = (new \Illuminate\Events\Dispatcher($app))->setQueueResolver(function () use ($app) {
                return $app->make(QueueFactoryContract::class);
            });

            $this->registry->alias('services.events', $event);

            return $event;
        });

        $this->app->alias('events', Dispatcher::class);
        $this->app->alias('events', \Illuminate\Contracts\Events\Dispatcher::class);
    }
}
