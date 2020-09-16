<?php

namespace App\Domains\Common\Support;

use App\Domains\Common\Logics\CronJobDescriptionLogic;
use App\Events\CommandFinished;
use App\Events\CommandStarting;

class CommandInfo
{
    protected static $startTime = [];
    protected $noRecordCommands = [
        'queue:work', 'queue:redis:consumer', 'optimizer',
        'tinker', 'hello', 'make:controller', 'make:test',
        'make:test_trait', 'test',
    ];

    public function handle($event)
    {
        if ($event instanceof CommandStarting) {
            static::$startTime[$event->command] = $event->dateTime;
        } elseif ($event instanceof CommandFinished && !in_array($event->command, $this->noRecordCommands)) {
            $args = $event->input->getArguments();
            /** @var CronJobDescriptionLogic $cronJob */
            $cronJob = di(CronJobDescriptionLogic::class);
            $cronJob->saveJobLog($event->command, $args, static::$startTime[$event->command], $event->exitCode);
        }
    }
}
