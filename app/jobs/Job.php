<?php

namespace App\Jobs;

use App\Services\Laravel\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\Job as QueueJob;
use Illuminate\Queue\SerializesModels;

abstract class Job
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    abstract public function handle(QueueJob $job);
}
