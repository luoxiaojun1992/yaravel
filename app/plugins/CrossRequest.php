<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Yaf\Plugin_Abstract as YafPlugin;
use Yaf\Request_Abstract as YafRequest;
use Yaf\Response_Abstract as YafResponse;

/**
 * Class CrossRequestPlugin
 *
 * @author overtrue <i@overtrue.me>
 */
class CrossRequestPlugin extends YafPlugin
{
    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerStartup(YafRequest $request, YafResponse $response)
    {
        /* 在路由之前执行,这个钩子里，你可以做url重写等功能 */
        $origin = Request::header('origin') ?: '';
        $allow_origin = explode(',', Config::get('auth.cross_domains', ''));
        if (in_array($origin, $allow_origin) || in_array('*', $allow_origin)) {
            if ($origin) {
                if ($response) {
                    $response->setHeader(
                        'Access-Control-Allow-Origin', $origin, false, null
                    );
                } else {
                    header('Access-Control-Allow-Origin:' . $origin, false, null);
                }
            } else {
                if ($response) {
                    $response->setHeader(
                        'Access-Control-Allow-Origin', '*', false, null
                    );
                } else {
                    header('Access-Control-Allow-Origin:*', false, null);
                }
            }
        }
        if (Request::method() === 'OPTIONS') {
            throw new \App\Exceptions\EmptyHttpException(
                200
            );
        }
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerShutdown(YafRequest $request, YafResponse $response)
    {
        /* 路由完成后，在这个钩子里，你可以做登陆检测等功能*/
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function dispatchLoopStartup(YafRequest $request, YafResponse $response)
    {
        //
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function preDispatch(YafRequest $request, YafResponse $response)
    {
        //
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function postDispatch(YafRequest $request, YafResponse $response)
    {
        //
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function dispatchLoopShutdown(YafRequest $request, YafResponse $response)
    {
        /* final hook
           in this hook user can do login or implement layout */
    }
}
