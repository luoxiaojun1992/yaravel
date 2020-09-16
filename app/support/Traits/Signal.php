<?php

namespace App\Support\Traits;

trait Signal
{
    /**
     * @return bool
     */
    protected function supportAsyncSignal()
    {
        return \App\Support\Signal::supportAsyncSignal();
    }

    /**
     * @param int $signal
     * @param callable $callback
     */
    protected function signal($signal, $callback)
    {
        \App\Support\Signal::signal($signal, $callback);
    }
}
