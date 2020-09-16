<?php

namespace App\Consts;

class Errors
{
    //错误code
    const NO_ERROR = 0; //没有错误
    const ERROR = 1; //未知错误

    //错误消息
    public static $msg = [
        self::NO_ERROR => 'success',
    ];

    /**
     * 根据错误码获取消息提示
     *
     * @param  $error
     * @return mixed|string
     */
    public static function msg($error)
    {
        return isset(self::$msg[$error]) ? self::$msg[$error] : '未知错误,code:' . $error;
    }
}
