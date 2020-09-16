<?php

namespace App\Support;

class PhpSapi
{
    /**
     * @return bool
     */
    public static function isCli()
    {
        return in_array(php_sapi_name(), ['phpdbg', 'cli']);
    }

    /**
     * @return bool
     */
    public static function isCliServer()
    {
        return in_array(php_sapi_name(), ['cli-server']);
    }
}
