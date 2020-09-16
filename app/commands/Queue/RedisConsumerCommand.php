<?php

namespace App\Commands\Queue;

use App\Commands\Base\BaseCommand;

/**
 * Class RedisConsumerCommand
 *
 * 不经过框架的queue组件，直接通过redis的list进行队列数据的消费
 * 在某些场景，方便、快速、简单地使用redis作为消息队列
 * 具体业务消费脚本应该继承这个类，覆写属性和consumeCallback方法
 *
 * @package App\Commands\Queue
 */
class RedisConsumerCommand extends BaseCommand
{
    protected $logSwitch = false;
    protected $outputSwitch = false;
    protected $cmdProxySwitch = false;
    protected $rootInfoAlert = false;

    //业务消费可能需要覆写的属性
    protected $queue = 'test';
    protected $popMethod = 'rpop';
    protected $redisConnection = 'queue';
    protected $signature = 'queue:redis:consumer';
    protected $description = '消费redis队列';
    protected $consumeBatch = 500;
    protected $idle = 1;

    public function handle()
    {
        $this->consume([$this, 'consumeCallback']);
    }

    /**
     * @param $messages
     */
    protected function consumeCallback($messages)
    {
        $this->info('Consume callback handle:' . json_encode($messages));
    }

    protected function deSerialize($message)
    {
        $deSerializedMessage = json_decode($message, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $deSerializedMessage : null;
    }

    /**
     * @param $callback
     * @throws \Throwable
     */
    protected function consume($callback)
    {
        $messageBuffer = [];
        $messageBufferSize = 0;

        $redisConnection = \RedisFacade::connection($this->redisConnection);

        while (true) {
            if ($this->popMethod === 'rpop') {
                $message = $redisConnection->rpop($this->queue);
            } else {
                $message = $redisConnection->lpop($this->queue);
            }

            if (!$message) {
                if ($messageBufferSize > 0) {
                    $this->callWithLog(function () use (
                        $callback, &$messageBuffer, &$messageBufferSize
                    ) {
                        $this->info('Flush message buffer');
                        call_user_func($callback, $messageBuffer);
                        $messageBuffer = [];
                        $messageBufferSize = 0;
                    }, 'flush_message_buffer', false, false, true, true);
                }

                sleep($this->idle);
                continue;
            }

            $this->callWithLog(function () use (
                $redisConnection, &$messageBuffer, &$messageBufferSize, $callback, $message
            ) {
                $this->info('Redis queue message:' . $message);
                $this->addBizInfo('redis_queue_message', $message);

                $deSerializedMessage = $this->deSerialize($message);
                if (!$deSerializedMessage) {
                    $this->callWithLog(function () use (
                        $callback, &$messageBuffer, &$messageBufferSize, $message
                    ) {
                        $this->error('Redis queue message error, content:' . $message);
                        $this->addBizInfo('redis_queue_message', $message);
                        if ($messageBufferSize > 0) {
                            $this->info('Flush message buffer caused by deSerialize error');
                            $this->addBizInfo('flush_message_buffer', true);
                            call_user_func($callback, $messageBuffer);
                            $messageBuffer = [];
                            $messageBufferSize = 0;
                        }
                    }, 'flush_message_buffer_by_deserialize_error', true, false, true, true);

                    return;
                }

                ++$messageBufferSize;
                $messageBuffer[] = $deSerializedMessage;

                if ($messageBufferSize >= $this->consumeBatch) {
                    call_user_func($callback, $message);
                    $messageBuffer = [];
                    $messageBufferSize = 0;
                }
            }, 'consume', false, false, true, true);
        }
    }
}
