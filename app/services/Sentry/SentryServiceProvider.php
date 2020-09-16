<?php

namespace App\Services\Sentry;

use App\Services\Log\Manager;
use App\Services\Providers\AbstractLaravelProvider;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;
use Sentry\SentryLaravel\SentryLaravel;
use Sentry\SentryLaravel\SentryLaravelEventHandler;

class SentryServiceProvider extends AbstractLaravelProvider
{
    protected $defer = true;

    /**
     * Abstract type to bind Sentry as in the Service Container.
     *
     * @var string
     */
    public static $abstract = 'sentry';

    /**
     * Bind to the Laravel event dispatcher to log events.
     *
     * @param $app
     */
    protected function bindEvents($app)
    {
        $user_config = $app[static::$abstract . '.config'];

        $handler = new SentryLaravelEventHandler($app[static::$abstract], $user_config);

        $handler->subscribe($app->events);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $app->singleton(static::$abstract . '.config', function ($app) {
            // Make sure we don't crash when we did not publish the config file and the config is null
            return $app['config'][static::$abstract] ?: array();
        });

        $app->singleton(static::$abstract, function ($app) {
            $sentryConfig = $app[static::$abstract . '.config'];
            $basePath = ROOT_PATH;
            $client = SentryLaravel::getClient(array_merge(array(
                'environment' => $app['config']->get('app.env'),
                'prefixes' => array($basePath),
                'app_path' => $basePath,
                'excluded_app_paths' => array($basePath . DIRECTORY_SEPARATOR . 'vendor'),
            ), $sentryConfig));

            $this->registry->alias('services.' . static::$abstract, $client);

            return $client;
        });

        /** @var \Raven_Client $client */
        $client = $app->make(static::$abstract);
        $sentryConfig = $app[static::$abstract . '.config'];
        $logDriver = $sentryConfig['driver'] ?? static::$abstract;

        /** @var Manager $logManager */
        $logManager = $app->make('log_manager');
        $logManager->extend($logDriver, function () use ($client, $sentryConfig) {
            $channel = ($sentryConfig['log']['channel']) ?? ($this->app->make('config')->get('app.env'));
            $monolog = new Monolog($channel);
            $reportHandler = new \Monolog\Handler\RavenHandler($client);
            $reportHandler->setFormatter(new \Monolog\Formatter\LineFormatter("%message% %context% %extra%\n"));
            $monolog->pushHandler($reportHandler);
            //最后push，记录log时会先调用
            $crumbsHandler = new \Raven_Breadcrumbs_MonologHandler($client);
            $monolog->pushHandler($crumbsHandler);
            return new Writer($monolog, $this->app['events']);
        });

        $this->bindEvents($this->app);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [static::$abstract, 'log_manager'];
    }
}
