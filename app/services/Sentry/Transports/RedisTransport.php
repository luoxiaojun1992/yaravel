<?php

namespace App\Services\Sentry\Transports;

use Sentry\SentryLaravel\SentryLaravelServiceProvider;

class RedisTransport
{
    /** @var \Raven_Client */
    protected $raveClient;

    protected $sampleRate = 1;

    protected $redisOptions = [
        'queue_name' => 'queue:sentry:transport',
        'connection' => 'sentry',
    ];

    public static function handle($ravenClient, $data)
    {
        self::create($ravenClient, config(SentryLaravelServiceProvider::$abstract))->send($data);
    }

    public static function create($ravenClient, $config)
    {
        return new static($ravenClient, $config);
    }

    public function __construct($ravenClient, $config)
    {
        $this->raveClient = $ravenClient;
        $this->redisOptions = array_merge($this->redisOptions, $config['redis_options'] ?? []);
        $this->sampleRate = $this->raveClient->sample_rate;
    }

    public function send($data)
    {
        if (empty($data)) {
            return;
        }

        if (!$this->needSample()) {
            return;
        }

        $payload = json_encode([
            'data' => $data,
            'server' => $this->raveClient->server,
            'public_key' => $this->raveClient->public_key,
            'secret_key' => $this->raveClient->secret_key,
        ]);

        try {
            $this->enqueue($payload);
        } catch (\Throwable $e) {
            \Log::error(
                'Sentry redis transport error: exception message is ' . $e->getMessage() .
                ', exception trace is ' . $e->getTraceAsString() . ', payload is ' . $payload
            );
        }
    }

    protected function needSample()
    {
        return (rand(1, 100) / 100.0) <= ($this->sampleRate);
    }

    protected function enqueue($payload)
    {
        $redisClient = $this->getRedisClient();
        if (is_null($redisClient)) {
            \Log::error('Sentry redis transport error: redis client is null, payload is ' . $payload);
            return;
        }

        if (empty($this->redisOptions['queue_name'])) {
            \Log::error('Sentry redis transport error: redis queue name is empty, payload is ' . $payload);
            return;
        }

        $redisClient->lpush($this->redisOptions['queue_name'], $payload);
    }

    protected function getRedisClient()
    {
        if (!empty($this->redisOptions['connection'])) {
            return \RedisFacade::connection($this->redisOptions['connection']);
        }

        return null;
    }
}
