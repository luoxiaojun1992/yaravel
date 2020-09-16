<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Services\Exceptions\ExceptionHandler;
use App\Services\Exceptions\ExceptionHandlerProvider;
use Yaf\Plugin_Abstract as YafPlugin;
use Yaf\Request_Abstract as YafRequest;
use Yaf\Response_Abstract as YafResponse;

/**
 * Class ExceptionHandlerPlugin.
 *
 * @author overtrue <i@overtrue.me>
 */
class ExceptionHandlerPlugin extends YafPlugin
{
    /**
     * 异常处理.
     *
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerStartup(YafRequest $request, YafResponse $response)
    {
        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = Registry::get(ExceptionHandlerProvider::SERVICE_ID);
        $exceptionHandler->setRequest($request);
        $exceptionHandler->setResponse($response);
    }
}
