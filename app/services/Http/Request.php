<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Services\Http;

use App\Services\Http\Traits\InputCastedAccessor;
use App\Support\Arr;
use App\Support\Ip;
use App\Support\Str;
use App\Support\Traits\PhpSapi;
use Yaf\Dispatcher;
use Yaf\Request_Abstract as YafRequest;

/**
 * Class Request.
 *
 * @author overtrue <i@overtrue.me>
 */
class Request
{
    use InputCastedAccessor;
    use PhpSapi;

    /** @var string[] not prefixed with HTTP_ */
    const SPECIAL_HEADERS = [
        'CONTENT-LENGTH', 'CONTENT-MD5', 'CONTENT-TYPE', 'REMOTE-ADDR', 'SERVER-PORT', 'HTTPS',
    ];

    /** @var YafRequest */
    protected $yafRequest;

    protected $addedHeader = [];

    protected $setHeader = [];

    protected $deletedHeader = [];

    /**
     * 客户端请求IP.
     *
     * @var string
     */
    protected $clientIp = '';

    public static function createFromYafRequest(YafRequest $yafRequest)
    {
        return new static($yafRequest);
    }

    public function __construct(YafRequest $yafRequest)
    {
        $this->yafRequest = $yafRequest;
    }

    /**
     * @return YafRequest
     */
    public function getYafRequest(): YafRequest
    {
        return $this->yafRequest;
    }

    /**
     * @return $this
     */
    public function getInstance()
    {
        return $this;
    }

    /**
     * 获取请求的数据.
     *
     * @param mixed $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!is_null($query = $this->getQuery($key))) {
            return $query;
        } elseif (!is_null($post = $this->getPost($key))) {
            return $post;
        } else {
            return $this->getCookie($key, $default);
        }
    }

    /**
     * 检查是否存在请求的数据，包含null.
     *
     * @param mixed $key
     * @param bool  $strict
     *
     * @return bool
     */
    public function has($key, $strict = false)
    {
        return $this->assertHas( $this->get($key), $strict);
    }

    /**
     * 不包含null
     *
     * @param mixed $key
     * @return bool
     */
    public function exist($key)
    {
        return array_key_exists($key, $this->all());
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function getQuery($key, $default = null)
    {
        return $this->yafRequest->getQuery($key) ?? $default;
    }

    /**
     * @return array
     */
    public function allQuery()
    {
        return $_GET;
    }

    /**
     * 包含null
     *
     * @param mixed $key
     * @param bool $strict
     * @return bool
     */
    public function hasQuery($key, $strict = false)
    {
        return $this->assertHas($this->getQuery($key), $strict);
    }

    /**
     * 不包含null
     *
     * @param mixed $key
     * @return bool
     */
    public function existQuery($key)
    {
        return array_key_exists($key, $this->allQuery());
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function getPost($key, $default = null)
    {
        return $this->yafRequest->getPost($key) ?? ($this->allPost()[$key] ?? $default);
    }

    /**
     * @return array
     */
    public function allPost()
    {
        $data = [];

        $contentType = $this->header('CONTENT-TYPE');

        if (Str::startsWith($contentType, 'application/x-www-form-urlencoded')
            && in_array($this->method(), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($this->raw(), $data);
        } elseif (Str::startsWith($contentType, 'application/json')) {
            $data = json_decode($this->raw(), true);
        }

        return array_merge($_POST, $data);
    }

    /**
     * 包含null
     *
     * @param mixed $key
     * @param bool $strict
     * @return bool
     */
    public function hasPost($key, $strict = false)
    {
        return $this->assertHas($this->getPost($key), $strict);
    }

    /**
     * 不包含null
     *
     * @param mixed $key
     * @return bool
     */
    public function existPost($key)
    {
        return array_key_exists($key, $this->allPost());
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function getEnv($key, $default = null)
    {
        return $this->yafRequest->getEnv($key) ?? $default;
    }

    /**
     * @return array
     */
    public function allEnv()
    {
        return $_ENV;
    }

    /**
     * 包含null
     *
     * @param mixed $key
     * @param bool $strict
     * @return bool
     */
    public function hasEnv($key, $strict = false)
    {
        return $this->assertHas($this->getEnv($key), $strict);
    }

    /**
     * 不包含null
     *
     * @param mixed $key
     * @return bool
     */
    public function existEnv($key)
    {
        return array_key_exists($key, $this->allEnv());
    }

    /**
     * @param mixed $key 大写
     * @param mixed $default
     * @return mixed
     */
    public function getServer($key, $default = null)
    {
        return $this->yafRequest->getServer(strtoupper($key)) ?? $default;
    }

    /**
     * @return array
     */
    public function allServer()
    {
        return $_SERVER;
    }

    /**
     * 包含null
     *
     * @param mixed $key
     * @param bool $strict
     * @return bool
     */
    public function hasServer($key, $strict = false)
    {
        return $this->assertHas($this->getServer(strtoupper($key)), $strict);
    }

    /**
     * 不包含null
     *
     * @param mixed $key
     * @return bool
     */
    public function existServer($key)
    {
        return array_key_exists($key, $this->allServer());
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function getCookie($key, $default = null)
    {
        return $this->yafRequest->getCookie($key) ?? $default;
    }

    /**
     * @return array
     */
    public function allCookie()
    {
        return $_COOKIE;
    }

    /**
     * 包含null
     *
     * @param mixed $key
     * @param bool $strict
     * @return bool
     */
    public function hasCookie($key, $strict = false)
    {
        return $this->assertHas($this->getCookie(strtoupper($key)), $strict);
    }

    /**
     * 不包含null
     *
     * @param mixed $key
     * @return bool
     */
    public function existCookie($key)
    {
        return array_key_exists($key, $this->allCookie());
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function getFile($key, $default = null)
    {
        return $this->yafRequest->getFiles($key) ?? $default;
    }

    /**
     * @return array
     */
    public function allFiles()
    {
        return $_FILES;
    }

    /**
     * 包含null.
     *
     * @param mixed $name
     * @param bool $strict
     *
     * @return bool
     */
    public function hasFile($name, $strict = false)
    {
        return $this->assertHas($this->getFile($name), $strict);
    }

    /**
     * 判断是否有正常上传文件.
     *
     * @param mixed $name
     * @return bool
     */
    public function validFile($name)
    {
        $file = $this->getFile($name);
        if (!$this->assertHas($file)) {
            return false;
        }

        return is_uploaded_file($file['tmp_name']);
    }

    /**
     * 不包含null
     *
     * @param mixed $name
     * @return bool
     */
    public function existFile($name)
    {
        return array_key_exists($name, $this->allFiles());
    }

    /**
     * 获取全部请求参数.
     *
     * @return array
     */
    public function all()
    {
        return array_merge($_REQUEST, $this->allPost());
    }

    /**
     * 没传某个参数或者参数为空.
     *
     * @param mixed $key
     * @param bool   $strict
     *
     * @return bool
     */
    public function without($key, $strict = false)
    {
        return !$this->has($key, $strict);
    }

    /**
     * 检查参数确认传了并且不空。
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function filled($key)
    {
        return $this->has($key, false);
    }

    /**
     * 返回 REQUEST_URI (不带 query).
     *
     * @return string|null
     */
    public function path()
    {
        $uri = $this->yafRequest->getRequestUri();
        if (!Str::startsWith($uri, '/')) {
            $uri = ('/' . $uri);
        }
        if (Str::contains($uri, '?')) {
            $pathInfo = parse_url($uri);
            if (isset($pathInfo['path'])) {
                return $pathInfo['path'];
            }
        }
        return $uri;
    }

    /**
     * @param array|string $prefixArr
     * @param bool $caseSensitive
     * @param callable $matchCallback
     * @return bool
     */
    public function matchPath($prefixArr, $caseSensitive = true, $matchCallback = null)
    {
        $path = $this->path();

        foreach ((array)$prefixArr as $i => $prefix) {
            if (Str::startsWith($path, $prefix, $caseSensitive)) {
                if (is_callable($matchCallback)) {
                    call_user_func($matchCallback, $prefix, $i);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * 请求 URI.
     *
     * @return string|null
     */
    public function uri()
    {
        return $this->getServer('REQUEST_URI');
    }

    /**
     * 请求url，不包含query string
     *
     * @return string
     */
    public function url()
    {
        $url = $this->httpHost();
        $schema = $this->schema();
        if (!Str::startsWith($url, $schema . '://')) {
            $url = ($schema . '://' . $url);
        }
        $serverPort = $this->serverPort();
        if (!Str::contains($url, ':' . $serverPort)) {
            $url = ($url . ':' . $serverPort);
        }
        return rtrim($url, '/') . $this->path();
    }

    /**
     * @return mixed|null
     */
    public function queryString()
    {
        return $this->getServer('QUERY_STRING');
    }

    /**
     * 请求url，包含query string
     *
     * @return string
     */
    public function fullUrl()
    {
        $queryString = $this->queryString();
        return $this->url() . (is_null($queryString) ? '' : '?') . ($queryString ?? '');
    }

    /**
     * 只获取指定列表的请求数据.
     *
     * @param array ...$keys
     *
     * @return array
     */
    public function only(...$keys)
    {
        return Arr::only(
            $this->all(),
            is_array($keys[0] ?? null) ? $keys[0] : $keys
        );
    }

    /**
     * 获取指定参数，保证 key 名，不存在时值为 null.
     *
     * @param array ...$keys
     *
     * @return array
     */
    public function keep(...$keys)
    {
        if (is_array($keys[0] ?? null)) {
            $keys = $keys[0];
        }

        return array_merge(array_combine($keys, array_pad([], count($keys), null)), Arr::only($this->all(), $keys));
    }

    /**
     * 排除指定参数.
     *
     * @param array ...$keys
     *
     * @return array
     */
    public function except(...$keys)
    {
        return Arr::except(
            $this->all(),
            is_array($keys[0] ?? null) ? $keys[0] : $keys
        );
    }

    /**
     * 返回请求方式：GET/POST/DELETE...
     *
     * @return string
     */
    public function method()
    {
        return strtoupper($this->yafRequest->getMethod() ?? 'GET');
    }

    /**
     * 获取单个请求头.
     *
     * @param mixed $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function header($key, $default = null)
    {
        $upperKey = strtoupper($key);

        if (array_key_exists($upperKey, $this->deletedHeader)) {
            return $default;
        }

        if (array_key_exists($upperKey, $this->setHeader)) {
            return $this->setHeader[$upperKey][count($this->setHeader[$upperKey]) - 1];
        }

        if (array_key_exists($upperKey, $this->addedHeader)) {
            return $this->addedHeader[$upperKey][count($this->addedHeader[$upperKey]) - 1];
        }

        $upperUnderlineKey = strtoupper(str_replace('-', '_', $key));

        $headerPrefix = 'HTTP_';

        if (!in_array($upperKey, static::SPECIAL_HEADERS)) {
            $upperUnderlineKey = ($headerPrefix . $upperUnderlineKey);
        }

        $header = $this->getServer($upperUnderlineKey);

        if (is_null($header)) {
            if (in_array($upperKey, static::SPECIAL_HEADERS)) {
                $upperUnderlineKey = ($headerPrefix . $upperUnderlineKey);
                $header = $this->getServer($upperUnderlineKey);
            }
        }

        return $header ?? $default;
    }

    /**
     * 包含null
     *
     * @param mixed $name
     * @param bool $strict
     * @return bool
     */
    public function hasHeader($name, $strict = false)
    {
        return $this->assertHas($this->header(strtoupper($name)), $strict);
    }

    /**
     * 不包含null
     *
     * @param mixed $name
     * @return bool
     */
    public function existHeader($name)
    {
        return array_key_exists($name, $this->headers());
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function addHeader($key, $value)
    {
        $upperKey = strtoupper($key);
        if (array_key_exists($upperKey, $this->deletedHeader)) {
            unset($this->deletedHeader[$upperKey]);
        } elseif (array_key_exists($upperKey, $this->setHeader)) {
            $this->addedHeader[$upperKey] = $this->setHeader[$upperKey];
            unset($this->setHeader[$upperKey]);
        } elseif (!array_key_exists($upperKey, $this->addedHeader)) {
            $headerPrefix = 'HTTP_';
            $processedKey = str_replace('_', '-', $upperKey);
            if (!in_array($upperKey, static::SPECIAL_HEADERS)) {
                $processedKey = ($headerPrefix . $processedKey);
            }
            $header = $this->getServer($processedKey);
            if (is_null($header)) {
                if (in_array($upperKey, static::SPECIAL_HEADERS)) {
                    $processedKey = ($headerPrefix . $processedKey);
                    $header = $this->getServer($processedKey);
                }
            }
            if (!is_null($header)) {
                $this->addedHeader[$upperKey] = [$header];
            }
        }
        $this->addedHeader[$upperKey][] = $value;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function setHeader($key, $value)
    {
        $upperKey = strtoupper($key);
        $this->setHeader[$upperKey] = [$value];
        if (array_key_exists($upperKey, $this->addedHeader)) {
            unset($this->addedHeader[$upperKey]);
        }
        if (array_key_exists($upperKey, $this->deletedHeader)) {
            unset($this->deletedHeader[$upperKey]);
        }
    }

    /**
     * @param mixed $key
     */
    public function delHeader($key)
    {
        $upperKey = strtoupper($key);
        if (array_key_exists($upperKey, $this->setHeader)) {
            unset($this->setHeader[$upperKey]);
        }
        if (array_key_exists($upperKey, $this->addedHeader)) {
            unset($this->addedHeader[$upperKey]);
        }
        $this->deletedHeader[$upperKey] = true;
    }

    /**
     * 获取 headers.
     *
     * @return array
     */
    public function headers()
    {
        $headers = [];

        $headerPrefix = 'HTTP_';

        foreach ($this->allServer() as $key => $value) {
            $processedKey = $key;
            $hasPrefix = false;
            if (stripos($key, $headerPrefix) === 0) {
                $hasPrefix = true;
                $processedKey = substr($processedKey, strlen($headerPrefix));
            }
            $processedKey = str_replace('_', '-', $processedKey);

            if ($hasPrefix) {
                $headers[$processedKey][] = $value;
            } elseif (in_array($processedKey, static::SPECIAL_HEADERS)) {
                $headers[$processedKey][] = $value;
            }
        }

        foreach ($this->addedHeader as $key => $values) {
            $headers[$key] = $values;
        }

        foreach ($this->setHeader as $key => $values) {
            $headers[$key] = $values;
        }

        foreach ($this->deletedHeader as $key => $value) {
            if (array_key_exists($key, $headers)) {
                unset($headers[$key]);
            }
        }

        return $headers;
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    public function bearerToken()
    {
        $header = $this->header('Authorization', '');

        $prefix = 'Bearer ';
        if (Str::startsWith($header, $prefix)) {
            return ltrim(Str::substr($header, Str::length($prefix)));
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function authToken()
    {
        return $this->header('Authorization');
    }

    /**
     * 获取客户端 IP.
     *
     * @return string|null
     */
    public function ip()
    {
        if ($this->clientIp) {
            return $this->clientIp;
        } elseif ($forwardedIp = $this->header('X-FORWARDED-FOR')) {
            if (is_array($forwardedIp)) {
                $forwardedIp = ($forwardedIp[0] ?? '');
            }
            return $this->clientIp = trim(explode(',', $forwardedIp)[0]);
        } elseif ($realIp = $this->header('X-REAL-IP')) {
            if (is_array($realIp)) {
                $realIp = ($realIp[0] ?? '');
            }
            return $this->clientIp = trim(explode(',', $realIp)[0]);
        } elseif ($remoteAddr = $this->getServer('REMOTE_ADDR')) {
            if (is_array($remoteAddr)) {
                $remoteAddr = ($remoteAddr[0] ?? '');
            }
            return $this->clientIp = trim(explode(',', $remoteAddr)[0]);
        } else {
            return $this->clientIp;
        }
    }

    /**
     * @return bool
     */
    public function isIpV6()
    {
        return substr_count($this->ip(), ':') > 1;
    }

    /**
     * @param mixed $ips
     * @return bool
     */
    public function checkIp($ips)
    {
        return Ip::checkIp($this->ip(), $ips);
    }

    /**
     * 获取请求时间.
     *
     * @return int
     */
    public function time()
    {
        return $this->getServer('REQUEST_TIME') ?? time();
    }

    /**
     * 获取当前模块名称.
     *
     * @return string|null
     */
    public function module()
    {
        return $this->yafRequest->getModuleName();
    }

    /**
     * 获取当前控制器名称.
     *
     * @return string|null
     */
    public function controller()
    {
        return $this->yafRequest->getControllerName();
    }

    /**
     * 获取当前action名称.
     *
     * @return string|null
     */
    public function action()
    {
        return $this->yafRequest->getActionName();
    }

    /**
     * @return string
     */
    public function route()
    {
        return strtolower($this->module()) . '/' . strtolower($this->controller()) . '/' . strtolower($this->action());
    }

    /**
     * @return bool
     */
    public function hasRoute()
    {
        return (!is_null($this->module())) && (!is_null($this->controller())) && (!is_null($this->action()));
    }

    /**
     * @return string
     */
    public function controllerAction()
    {
        return strtolower($this->controller()) . '/' . strtolower($this->action());
    }

    /**
     * 获取文件流.
     *
     * @return string
     */
    public function raw()
    {
        return file_get_contents('php://input');
    }

    /**
     * @return string|null
     */
    public function protocolVersion()
    {
        return $this->getServer('SERVER_PROTOCOL');
    }

    /**
     * @return int|null
     */
    public function clientPort()
    {
        return $this->getServer('REMOTE_PORT');
    }

    /**
     * @return string
     */
    public function schema()
    {
        return $this->isSecureConnection() ? 'https' : 'http';
    }

    /**
     * @return bool
     */
    public function isSecureConnection()
    {
        $https = $this->getServer('HTTPS');
        if (isset($https) && (strcasecmp($https, 'on') === 0 || $https == 1)) {
            return true;
        }

        $secureProtocolHeaders = [
            'X-Forwarded-Proto' => ['https'], // Common
            'Front-End-Https' => ['on'], // Microsoft
        ];
        foreach ($secureProtocolHeaders as $header => $values) {
            if (($headerValue = $this->header($header)) !== null) {
                foreach ($values as $value) {
                    if (strcasecmp($headerValue, $value) === 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function httpHost()
    {
        if (!$httpHost = $this->getServer('HTTP_HOST')) {
            if (!$httpHost = $this->getServer('SERVER_NAME')) {
                $httpHost = $this->getServer('SERVER_ADDR');
            }
        }

        return $httpHost ?: '';
    }

    /**
     * @return int|mixed|null
     */
    public function serverPort()
    {
        if (!$host = $this->header('HOST')) {
            return $this->getServer('SERVER_PORT');
        }

        if ('[' === $host[0]) {
            $pos = strpos($host, ':', strrpos($host, ']'));
        } else {
            $pos = strrpos($host, ':');
        }

        if (false !== $pos) {
            return (int) substr($host, $pos + 1);
        }

        return 'https' === $this->schema() ? 443 : 80;
    }

    /**
     * @return string|null
     */
    public function lang()
    {
        return $this->yafRequest->getLanguage();
    }

    /**
     * 动态转发到 Yaf\Request_Simple.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return forward_static_call_array([Dispatcher::getInstance()->getRequest(), $method], $args);
    }

    /**
     * @param mixed $value
     * @param bool $strict
     * @return bool
     */
    protected function assertHas($value, $strict = false)
    {
        if ($strict) {
            return !is_null($value);
        }

        if (is_array($value)) {
            return !empty($value);
        }

        /*
         * 防止值为 int 0, string '0' 时造成误判，所以使用 strlen() 来判断是否有该请求参数
         *
         * 特殊：0 === strlen(false) === strlen(null) === strlen('')
         *      1 === strlen(0) === strlen('0') === strlen(1)
         *
         * 数字：2 === strlen(12)，3 === strlen(0.2)
         *
         * 其它：5 === strlen('false'), 4 === strlen('true') === strlen('null')
         */
        return strlen($value) > 0;
    }
}
