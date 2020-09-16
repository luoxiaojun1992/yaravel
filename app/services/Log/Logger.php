<?php

namespace App\Services\Log;

use App\Support\Traits\PhpSapi;
use App\Support\Traits\Signal;
use Illuminate\Log\Writer;

/**
 * Class Logger
 *
 * @mixin Writer
 * @package App\Services\Log
 */
class Logger
{
    use Signal;
    use PhpSapi;

    public static $hasTicker = false;

    protected $logBuffer = [];
    protected $logBufferSize = 0;

    /** @var Writer */
    protected $writer;

    protected $config = [];

    /**
     * Logger constructor.
     *
     * @param Writer $writer
     * @param array $config
     */
    public function __construct($writer, $config = [])
    {
        $this->writer = $writer;
        $this->config = $config;

        //Not useful for swoole
        if ($this->isCli()) {
            if ($this->isDefer()) {
                if (!static::$hasTicker) {
                    if ($this->supportAsyncSignal()) {
                        $this->signal(SIGINT, function () {
                            foreach (\Log::channels() as $channel) {
                                $channel->flushLogs();
                            }
                        });
                        static::$hasTicker = true;
                    }
                }
            }
        }
    }

    /**
     * Log an emergency message to the logs.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Log an error message to the logs.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Log a notice to the logs.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->isDefer()) {
            $this->logBuffer[$level][] = [$message, $context];
            $this->incrAndCheckBuffer();
        } else {
            $this->getWriter()->log($level, $message, $context);
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func([$this->writer, $name], $arguments);
    }

    public function flushLogs()
    {
        foreach ($this->logBuffer as $logLevel => $logInfos) {
            foreach ($logInfos as $logInfo) {
                list($message, $context) = $logInfo;
                $this->getWriter()->log($logLevel, $message, $context);
            }
        }
        $this->logBufferSize = 0;
    }

    public function __destruct()
    {
        $this->flushLogs();
    }

    /**
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }

    /**
     * @return bool
     */
    public function isDefer()
    {
        return $this->config['is_defer'] ?? false;
    }

    /**
     * @return int
     */
    protected function maxBufferSize()
    {
        return $this->config['max_buffer_size'] ?? 10;
    }

    protected function incrBufferSize()
    {
        ++$this->logBufferSize;
    }

    protected function decrBufferSize()
    {
        --$this->logBufferSize;
    }

    protected function incrAndCheckBuffer()
    {
        $this->incrBufferSize();
        if ($this->logBufferSize >= $this->maxBufferSize()) {
            $this->flushLogs();
        }
    }

    /**
     * @return int
     */
    protected function bufferTimeout()
    {
        return $this->config['buffer_timeout'] ?? 10;
    }
}
