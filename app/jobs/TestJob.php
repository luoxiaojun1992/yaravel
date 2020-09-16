<?php

namespace App\Jobs;

use Illuminate\Queue\Jobs\Job as QueueJob;

class TestJob extends Job
{
    protected $data;

    public function handle(QueueJob $job)
    {
        $this->data = $job->payload()['data'];
        $this->process();
    }

    protected function process()
    {
        var_dump($this->data);
    }
}
