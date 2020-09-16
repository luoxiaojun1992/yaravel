<?php

namespace App\Support;

class Signal
{
    /**
     * @return bool
     */
    public static function supportAsyncSignal()
    {
        if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            if (extension_loaded('pcntl')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $signal
     * @param callable $callback
     */
    public static function signal($signal, $callback)
    {
        pcntl_async_signals(true);
        pcntl_signal($signal, $callback);
    }
}
