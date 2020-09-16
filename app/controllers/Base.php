<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Support\Presenters\PresenterInterface;
use App\Services\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Yaf\Controller_Abstract as YafController;

/**
 * class BaseController.
 *
 * @author overtrue <i@overtrue.me>
 */
abstract class BaseController extends YafController
{
    use \App\Support\Traits\Translator;

    /**
     * Headers.
     *
     * <pre>
     * [
     *    'content-type' => 'application/json;charset=utf-8'
     * ]
     * </pre>
     *
     * @var array
     */
    protected $headers = [];

    /**
     * 初始化.
     */
    public function init()
    {
        //
    }

    /**
     * 主逻辑.
     */
    public function indexAction()
    {
        $response = $this->handle();

        return $this->handleResponse($response);
    }

    /**
     * 业务主逻辑.
     *
     * @return array
     */
    public function handle()
    {
        return [];
    }

    /**
     * 添加 header.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function header(string $name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * 获取设置的 headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 调用其它控制器.
     *
     * @param string $controller
     *
     * @return mixed
     */
    public function call($controller)
    {
        $controller = $this->normalizeControllerName($controller);

        return (new $controller($this->getRequest(), $this->getResponse(), $this->getView()))->indexAction();
    }

    /**
     * 处理响应内容.
     *
     * @param callable|array|string|\Psr\Http\Message\ResponseInterface $response
     *
     * @return mixed
     */
    protected function handleResponse($response)
    {
        if (is_callable($response)) {
            $response = $response($this);
        }

        if ($response instanceof PresenterInterface) {
            $response = $response->toArray();
        }

        if ($response instanceof \App\Support\Transformers\BaseTransformer) {
            $response = $response->present();
        }

        if (is_array($response)) {
            $response = json_encode($response);
            $this->header('Content-Type', 'application/json;charset=utf-8');
        }

        if (defined('TESTING')) {
            return;
        }

        if (!($response instanceof ResponseInterface)) {
            $response = new Response(200, $this->headers, $response);
        }

        //兼容Yaf的Response输出逻辑
        $response->setYafResponse($this->getResponse());
        $response->send();

        return $response;
    }

    /**
     * 接口返回.
     *
     * @param int $code
     * @param string $msg
     * @param null $data
     * @param int $statusCode
     * @param array $headers
     * @param null|\App\Support\Transformers\BaseTransformer $transformer
     * @param bool $translateMsg
     * @param int $jsonOptions
     * @param bool $allowNullData
     */
    protected function apiResponse(
        $code = \App\Consts\Errors::NO_ERROR,
        $msg = '',
        $data = null,
        $statusCode = 200,
        $headers = [],
        $transformer = null,
        $translateMsg = false,
        $jsonOptions = JSON_UNESCAPED_UNICODE,
        $allowNullData = true
    )
    {
        $msg = ($msg ?: \App\Consts\Errors::msg($code));

        if ($translateMsg) {
            $msg = static::__error($msg);
        }

        if (is_null($transformer)) {
            $transformer = (new \App\Support\Transformers\BaseTransformer(null))
                ->setData($data);
        } else {
            if (!$transformer->hasData()) {
                $transformer->setData($data);
            }
        }

        $result = [
            'code' => $code,
            'msg' => $msg,
            'message' => $msg,
        ];

        $transformedData = $transformer->present();
        if (is_null($transformedData)) {
            if ($allowNullData) {
                $result['data'] = $transformedData;
            }
        } else {
            $result['data'] = $transformedData;
        }

        $body = json_encode($result, $jsonOptions);
        $headers['Content-Type'] = 'application/json;charset=utf-8';

        $response = new Response(
            $statusCode,
            $headers,
            $body
        );

        $this->handleResponse($response);
    }

    /**
     * 接口失败返回.
     *
     * @param int $code
     * @param string $msg
     * @param int $statusCode
     * @param bool $translateMsg
     * @param int $jsonOptions
     */
    protected function apiFail(
        $code = \App\Consts\Errors::ERROR,
        $msg = '',
        $statusCode = 200,
        $translateMsg = false,
        $jsonOptions = JSON_UNESCAPED_UNICODE
    )
    {
        $this->apiResponse(
            $code,
            $msg,
            null,
            $statusCode,
            [],
            null,
            $translateMsg,
            $jsonOptions,
            false
        );
    }

    /**
     * 格式化控制器名称.
     *
     * @param string $controllerName
     *
     * @return string
     */
    public function normalizeControllerName($controllerName)
    {
        $replacements = [
            ' ' => '',
            '/' => ' ',
            '_' => ' ',
            'Controller' => '',
        ];

        $controller = str_replace(array_keys($replacements), $replacements, trim($controllerName));

        $controller = preg_replace_callback('/([^_\s])([A-Z])/', function ($matches) {
            return $matches[1].' '.$matches[2];
        }, $controller);

        return str_replace(' ', '_', ucwords($controller.'Controller'));
    }
}
