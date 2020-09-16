<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Class Response.
 *
 * @method static int       getStatusCode()
 * @method static string    getReasonPhrase()
 * @method static \App\Services\Http\Response  withStatus(int $code, string $reasonPhrase = '')
 * @method static void      setCookie(string $name, string $value = "", int $expire = 0, string $path = "", string $domain = "", bool $secure = false, bool $httpOnly = false)
 *
 * @author overtrue <i@overtrue.me>
 */
class Response extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'context.http.response';
    }
}
