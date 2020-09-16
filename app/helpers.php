<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\ErrorException;
use App\Services\Http\RedirectResponse;
use App\Services\Http\Response;

if (!function_exists('config')) {
    /**
     * Config 类的别名方法.
     *
     * @param string|array $property
     * @param mixed  $default
     *
     * @return mixed
     */
    function config($property, $default = null)
    {
        if (is_array($property)) {
            foreach ($property as $key => $val) {
                Config::set($key, $val);
            }

            return true;
        }

        return Config::get($property, $default);
    }
}

if (!function_exists('view')) {
    /**
     * @param string $template
     * @param array  $data
     *
     * @return string|Response
     */
    function view(string $template, array $data)
    {
        $engine = Registry::get('services.view');

        if (!$engine) {
            abort('No template engine found.');
        }

        $body = $engine->render($template, (array) $data);

        return new Response(200, ['content-type' => 'text/html;charset=utf-8'], $body);
    }
}

if (!function_exists('json')) {
    /**
     * @param array|object|string $data
     *
     * @return \App\Services\Http\Response
     */
    function json($data)
    {
        return new Response(200, ['content-type' => 'application/json;charset=utf-8'], json_encode($data));
    }
}

if (!function_exists('redirect')) {
    /**
     * @param string $targetUrl
     *
     * @return \App\Services\Http\RedirectResponse
     */
    function redirect(string $targetUrl)
    {
        return new RedirectResponse($targetUrl);
    }
}

if (!function_exists('abort')) {
    /**
     * 停止并抛出异常.
     *
     * @param string|array $message
     * @param int          $code
     *
     * @throws ErrorException
     */
    function abort($message = '系统错误', $code = 500)
    {
        if (is_array($message)) {
            $error = $message['error'] ?? false;
            $message = $error && is_string($error) ? $error : '未知错误';
        }

        throw new ErrorException($message, $code);
    }
}

if (!function_exists('running_unit_tests')) {
    /**
     * 是否的测试环境中.
     *
     * @return bool
     */
    function running_unit_tests()
    {
        return defined('TESTING') && TESTING;
    }
}

if (!function_exists('mdd')) {
    /**
     * mobile dump and die.
     *
     * @param array ...$args
     */
    function mdd(...$args)
    {
        ob_start();
        array_map('var_dump', $args);
        $content = html_entity_decode(strip_tags(ob_get_contents()));
        ob_end_clean();
        echo $content;
        exit;
    }
}

if (!function_exists('debug')) {
    /**
     * Debug 日志.
     *
     * @param string $message
     * @param array  $context
     */
    function debug($message, $context = [])
    {
        \Log::debug($message, $context);
    }
}

if (!function_exists('environment')) {
    /**
     * 获取当前运行环境名称：dev/production.
     *
     * @return string
     */
    function environment()
    {
        return Config::get('app')['env'];
    }
}

if (!function_exists('is_dev')) {
    /**
     * 是不是开发环境.
     *
     * @return bool
     */
    function is_dev()
    {
        return Config::get('app')['env'] == 'dev';
    }
}

if (!function_exists('is_production')) {
    /**
     * 是不是生产环境.
     *
     * @return bool
     */
    function is_production()
    {
        return Config::get('app')['env'] == 'production';
    }
}

if (!function_exists('is_debug')) {
    /**
     * 是不是调试模式.
     *
     * @return bool
     */
    function is_debug()
    {
        return Config::get('app')['debug'];
    }
}

if (!function_exists('app_name')) {
    /**
     * 获取应用名.
     *
     * @return string
     */
    function app_name()
    {
        return Config::get('app')['name'];
    }
}

if (!function_exists('timezone')) {
    /**
     * 获取应用时区.
     *
     * @return string
     */
    function timezone()
    {
        return Config::get('app')['timezone'];
    }
}

if (!function_exists('service')) {
    /**
     * 获取registry中注册的service.
     *
     * @param string $name
     *
     * @return mixed
     */
    function service($name)
    {
        return Registry::get('services.' . $name);
    }
}

if (!function_exists('di')) {
    /**
     * 获取registry中注册的资源.
     *
     * @param string $name
     * @param array $instanceArgs
     *
     * @return mixed
     */
    function di($name, $instanceArgs = [])
    {
        return Registry::get($name, $instanceArgs);
    }
}

if (!function_exists('laravel_di')) {
    /**
     * 获取registry中注册的laravel di.
     *
     * @return \Illuminate\Container\Container
     */
    function laravel_di()
    {
        return Registry::get('services.di');
    }
}

if (!function_exists('app')) {
    /**
     * 兼容laravel的container.
     *
     * @param string $abstract
     * @param array $parameters
     * @return \App\Services\Laravel\Container|mixed
     */
    function app($abstract = null, array $parameters = [])
    {
        /** @var \App\Services\Laravel\Container $container */
        $container = Registry::get('services.di');

        if (is_null($abstract)) {
            return $container;
        }

        return $container->make($abstract, $parameters);
    }
}

if (!function_exists('request')) {
    /**
     * 获取http request.
     *
     * @return \App\Services\Http\Request|null
     */
    function request()
    {
        if (\App\Support\PhpSapi::isCli()) {
            return null;
        } else {
            return Registry::get('context.http.request');
        }
    }
}

if (!function_exists('response')) {
    /**
     * 获取http response.
     *
     * @return \App\Services\Http\Response|null
     */
    function response()
    {
        if (\App\Support\PhpSapi::isCli()) {
            return null;
        } else {
            return Registry::get('context.http.response');
        }
    }
}

if (!function_exists('is_cli')) {
    /**
     * 判断当前是否以cli模式运行.
     *
     * @return mixed
     */
    function is_cli()
    {
        return \App\Support\PhpSapi::isCli();
    }
}

if (!function_exists('framework_version')) {
    /**
     * 获取框架版本.
     *
     * @return mixed
     */
    function framework_version()
    {
        return \Yaf\VERSION;
    }
}
