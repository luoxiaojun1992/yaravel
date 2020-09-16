<?php

namespace App\Services\Log;

use App\Services\Providers\AbstractLaravelProvider;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;

class LogServiceProvider extends AbstractLaravelProvider
{
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('log_manager', function () {
            $logManager = $this->createLogManager();
            $this->registry->alias('services.log_manager', $logManager);
            return $logManager;
        });
        $this->app->singleton('log', function () {
            $logger = $this->createLogger();
            $this->registry->alias('services.log', $logger);
            return $logger;
        });

        $this->app->alias('log', \Illuminate\Log\Writer::class);
        $this->app->alias('log', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('log', \Psr\Log\LoggerInterface::class);
    }

    protected function createLogManager()
    {
        $logManager = new Manager();

        $logConfig = $this->app->make('config')->get('logging');

        $defaultLogChannel = $logConfig['default_channel'];
        $logChannels = $logConfig['channels'];

        $logManager->setDefaultDriver($defaultLogChannel);

        foreach ($logChannels as $logChannelName => $logChannelConfig) {
            $logManager->extend($logChannelName, function () use ($logChannelName, $logChannelConfig) {
                $logger = new Logger(new Writer(
                    new Monolog($this->channel($logChannelConfig)), $this->app['events']
                ), $logChannelConfig);
                $this->configureHandler($logger->getWriter(), $logChannelConfig, $logChannelName);
                return $logger;
            });
        }

        return $logManager;
    }

    /**
     * Create the logger.
     *
     * @return \Illuminate\Log\Writer
     */
    public function createLogger()
    {
        /** @var Manager $logManager */
        $logManager = $this->app->make('log_manager');
        return $logManager->channel()->getWriter();
    }

    /**
     * Get the name of the log "channel".
     *
     * @param array $logChannelConfig
     *
     * @return string
     */
    protected function channel($logChannelConfig)
    {
        return ($logChannelConfig['channel']) ?? ($this->app->make('config')->get('app.env'));
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @param  array $logChannelConfig
     * @param  string $logChannelName
     * @return void
     */
    protected function configureHandler(Writer $log, $logChannelConfig, $logChannelName)
    {
        $this->{'configure'.ucfirst($this->handler($logChannelConfig)).'Handler'}($log, $logChannelConfig, $logChannelName);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @param  array $logChannelConfig
     * @param  string $logChannelName
     * @return void
     */
    protected function configureSingleHandler(Writer $log, $logChannelConfig, $logChannelName)
    {
        $log->useFiles(
            ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR .'logs' . DIRECTORY_SEPARATOR . 'laravel-' . php_sapi_name() . '-' . $logChannelName . '.log',
            $this->logLevel($logChannelConfig)
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @param  array $logChannelConfig
     * @param  string $logChannelName
     * @return void
     */
    protected function configureDailyHandler(Writer $log, $logChannelConfig, $logChannelName)
    {
        $log->useDailyFiles(
            ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR .'logs' . DIRECTORY_SEPARATOR . 'laravel-' . php_sapi_name() . '-' . $logChannelName . '.log', $this->maxFiles($logChannelConfig),
            $this->logLevel($logChannelConfig)
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @param  array $logChannelConfig
     * @param  string $logChannelName
     * @return void
     */
    protected function configureSyslogHandler(Writer $log, $logChannelConfig, $logChannelName)
    {
        $log->useSyslog('laravel-' . php_sapi_name() . '-' . $logChannelName, $this->logLevel($logChannelConfig));
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @param  array $logChannelConfig
     * @return void
     */
    protected function configureErrorlogHandler(Writer $log, $logChannelConfig)
    {
        $log->useErrorLog($this->logLevel($logChannelConfig));
    }

    /**
     * Get the default log handler.
     *
     * @param array $logChannelConfig
     *
     * @return string
     */
    protected function handler($logChannelConfig)
    {
        return $logChannelConfig['handler'] ?? 'daily';
    }

    /**
     * Get the log level for the application.
     *
     * @param array $logChannelConfig
     *
     * @return string
     */
    protected function logLevel($logChannelConfig)
    {
        return $logChannelConfig['level'] ?? 'debug';
    }

    /**
     * Get the maximum number of log files for the application.
     *
     * @param array $logChannelConfig
     *
     * @return int
     */
    protected function maxFiles($logChannelConfig)
    {
        return $logChannelConfig['max_files'] ?? 0;
    }

    public function provides()
    {
        return [
            'log_manager', 'log', \Illuminate\Log\Writer::class,
            \Illuminate\Contracts\Logging\Log::class,
            \Psr\Log\LoggerInterface::class,
        ];
    }
}
