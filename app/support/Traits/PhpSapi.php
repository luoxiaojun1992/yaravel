<?php

namespace App\Support\Traits;

trait PhpSapi
{
    /**
     * @return bool
     */
    protected function isCli()
    {
        return \App\Support\PhpSapi::isCli();
    }

    /**
     * @return bool
     */
    protected function isCliServer()
    {
        return \App\Support\PhpSapi::isCliServer();
    }
}
