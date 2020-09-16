<?php

namespace App\Domains\User\Support;

use App\Domains\Common\Support\Auth;
use App\Support\PhpSapi;

/**
 * Class Customer
 *
 * not useful for swoole
 *
 * @package App\Domains\User\Support
 */
class Customer
{
    protected static $currentUUID;

    public static function getCurrentUUID()
    {
        if (static::$currentUUID) {
            return static::$currentUUID;
        }

        if (!PhpSapi::isCli()) {
            return \Request::header('J-CustomerUUID');
        }

        return null;
    }

    public static function setCurrentUUID($uuid)
    {
        static::$currentUUID = $uuid;
    }

    public static function setCurrentUuidByMid($mid)
    {
        static::$currentUUID = Auth::jCustomerUUID($mid);
    }

    public static function clearCurrentMid($mid)
    {
        static::$currentUUID = null;
    }
}
