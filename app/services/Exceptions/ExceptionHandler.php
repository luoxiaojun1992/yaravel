<?php

namespace App\Services\Exceptions;

use App\Consts\Errors;
use App\Exceptions\EmptyHttpException;
use App\Exceptions\HttpException;
use App\Support\Traits\PhpSapi;
use Illuminate\Validation\ValidationException;
use \Throwable;
use Yaf\Exception\LoadFailed;
use Yaf\Exception\RouterFailed;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;

class ExceptionHandler
{
    use PhpSapi;

    protected $request;
    protected $response;
    protected $previousHandler;

    public function __construct($request = null, $response = null)
    {
        $this->request = $request;
        $this->response = $response;

        //Maybe not useful for swoole
        $this->previousHandler = set_exception_handler(function ($e) {
            $this->exceptionHandler($e);
            if ($this->previousHandler) {
                call_user_func($this->previousHandler, $e);
            }
        });
    }

    /**
     * 记录异常信息
     *
     * @param Throwable $e
     */
    public function report(Throwable $e)
    {
        if ($e instanceof HttpException) {
            return;
        }
        if ($e instanceof ValidationException) {
            return;
        }
        if ($e instanceof RouterFailed) {
            return;
        }
        if ($e instanceof LoadFailed\Module) {
            return;
        }
        if ($e instanceof LoadFailed\Controller) {
            return;
        }
        if ($e instanceof LoadFailed\Action) {
            return;
        }
        if ($e instanceof LoadFailed) {
            return;
        }

        $this->logReport($e);
        $this->sentryReport($e);
    }

    /**
     * 通过log记录异常信息
     *
     * @param Throwable $e
     */
    protected function logReport(Throwable $e)
    {
        \Log::error($e->getMessage() . '|' . $e->getTraceAsString());
    }

    /**
     * 通过sentry记录异常信息
     *
     * @param Throwable $e
     */
    protected function sentryReport(Throwable $e)
    {
        $sentryAbstractName = \App\Services\Sentry\SentryServiceProvider::$abstract;
        if (\Registry::has($sentryAbstractName)) {
            \Sentry::captureException($e);
        }
    }

    /**
     * @param Throwable $e
     * @return string
     */
    protected function transformException(Throwable $e)
    {
        if ($e instanceof EmptyHttpException) {
            return '';
        }

        $code = $e->getCode() ?: Errors::ERROR;
        $msg = $e->getMessage() ?: Errors::msg($code);

        return json_encode([
            'code' => $code,
            'msg' => $msg,
            'message' => $msg,
        ]);
    }

    protected function transformHeader(Throwable $e)
    {
        if ($e instanceof EmptyHttpException) {
            return [];
        }

        return ['content-type' => 'application/json;charset=utf-8'];
    }

    /**
     * 渲染输出错误信息
     *
     * @param Throwable $e
     */
    protected function render(Throwable $e)
    {
        $body = $this->transformException($e);

        if ($this->isCli()) {
            echo $body;
        } else {
            if ($e instanceof HttpException) {
                $httpStatusCode = $e->getStatusCode();
            } elseif ($e instanceof RouterFailed) {
                $httpStatusCode = 404;
            } elseif ($e instanceof LoadFailed\Module) {
                $httpStatusCode = 404;
            } elseif ($e instanceof LoadFailed\Controller) {
                $httpStatusCode = 404;
            } elseif ($e instanceof LoadFailed\Action) {
                $httpStatusCode = 404;
            } elseif ($e instanceof LoadFailed) {
                $httpStatusCode = 404;
            } else {
                $httpStatusCode = 500;
            }

            $response = (new \App\Services\Http\Response(
                $httpStatusCode,
                $this->transformHeader($e),
                $body
            ));

            if (is_null($this->response)) {
                $response->send();
            } else {
                $response->setYafResponse($this->response)->send();

                //可能无法执行入口文件的response
                $this->response->response();
            }
        }
    }

    /**
     * 异常处理器.
     *
     * @param Throwable $e
     */
    public function exceptionHandler(Throwable $e)
    {
        $this->report($e);
        $this->render($e);
    }

    /**
     * @param Request_Abstract $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param Response_Abstract $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
}
