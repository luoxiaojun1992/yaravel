<?php

namespace App\Jobs;

class TestJob extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        var_dump($this->data);
    }
}
