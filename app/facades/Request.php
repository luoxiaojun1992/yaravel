<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Yaf\Request_Abstract as YafRequest;

/**
 * Class Request.
 *
 * @author overtrue <i@overtrue.me>
 *
 * @method static YafRequest     getYafRequest()
 * @method static mixed          get(mixed $key, mixed $default = null)
 * @method static bool           has(mixed $key, bool $strict = false)
 * @method static bool           exist(mixed $key)
 * @method static mixed          getQuery(mixed $key, mixed $default = null)
 * @method static array          allQuery()
 * @method static bool           hasQuery(mixed $key, bool $strict = false)
 * @method static bool           existQuery(mixed $key)
 * @method static mixed          getPost(mixed $key, mixed $default = null)
 * @method static array          allPost()
 * @method static bool           hasPost(mixed $key, bool $strict = false)
 * @method static bool           existPost(mixed $key)
 * @method static mixed          getEnv(mixed $key, mixed $default = null)
 * @method static array          allEnv()
 * @method static bool           hasEnv(mixed $key, bool $strict = false)
 * @method static bool           existEnv(mixed $key)
 * @method static mixed          getServer(mixed $key, mixed $default = null)
 * @method static array          allServer()
 * @method static bool           hasServer(mixed $key, bool $strict = false)
 * @method static bool           existServer(mixed $key)
 * @method static mixed          getCookie(mixed $key, mixed $default = null)
 * @method static array          allCookie()
 * @method static bool           hasCookie(mixed $key, bool $strict = false)
 * @method static bool           existCookie(mixed $key)
 * @method static mixed          getFile(mixed $key, mixed $default = null)
 * @method static array          allFiles()
 * @method static bool           hasFile(mixed $name, bool $strict = false)
 * @method static bool           validFile(mixed $name)
 * @method static bool           existFile(mixed $name)
 * @method static array          all()
 * @method static bool           without(mixed $key, bool $strict = false)
 * @method static bool           filled(mixed $key)
 * @method static string         path()
 * @method static bool           matchPath(array|string $prefixArr, bool $caseSensitive = true, ?callable $matchCallback = null)
 * @method static string         uri()
 * @method static string         url()
 * @method static mixed|null     queryString()
 * @method static string         fullUrl()
 * @method static array          only(...$keys)
 * @method static array          keep(...$keys)
 * @method static array          except(...$keys)
 * @method static string         method()
 * @method static mixed          header(mixed $key, mixed $default = null)
 * @method static bool           hasHeader(mixed $name, bool $strict = false)
 * @method static bool           existHeader(mixed $name)
 * @method static void           addHeader(mixed $key, mixed $value)
 * @method static void           setHeader(mixed $key, mixed $value)
 * @method static void           delHeader(mixed $key)
 * @method static array          headers()
 * @method static string|null    bearerToken()
 * @method static string|null    authToken()
 * @method static string|null    ip()
 * @method static bool           isIpV6()
 * @method static bool           checkIp(mixed $ips)
 * @method static int|null       time()
 * @method static string|null    module()
 * @method static string|null    controller()
 * @method static string|null    action()
 * @method static string         route()
 * @method static bool           hasRoute()
 * @method static string         controllerAction()
 * @method static string         raw()
 * @method static string|null    protocolVersion()
 * @method static int|null       clientPort()
 * @method static string         schema()
 * @method static bool           isSecureConnection()
 * @method static string         httpHost()
 * @method static int|mixed|null  serverPort()
 * @method static int            int(mixed $key, mixed $default = null)
 * @method static float          float(mixed $key, mixed $default = null)
 * @method static bool           bool(mixed $key, bool $toInt = false, mixed $default = null)
 * @method static bool           bool2Int(mixed $key, mixed $default = null)
 * @method static int            abs(mixed $key, mixed $default = null)
 * @method static string         lang()
 * @method static \App\Services\Http\Request getInstance()
 */
class Request extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'context.http.request';
    }
}
