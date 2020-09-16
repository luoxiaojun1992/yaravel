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
 * Class IpRestrictionPlugin
 *
 * @author overtrue <i@overtrue.me>
 */
class IpRestrictionPlugin extends YafPlugin
{
    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerStartup(YafRequest $request, YafResponse $response)
    {
        /* 在路由之前执行,这个钩子里，你可以做url重写等功能 */
        $ipRestrictionConfigArr = config('auth.ip_restriction');

        foreach ($ipRestrictionConfigArr as $ipRestrictionConfig) {
            if (empty($ipRestrictionConfig['ips'])) {
                continue;
            }

            if (empty($ipRestrictionConfig['api_prefix'])) {
                continue;
            }

            $ips = explode(',', $ipRestrictionConfig['ips']);

            if (Request::matchPath(
                explode(',', $ipRestrictionConfig['api_prefix']),
                false,
                function ($prefix, $i) use ($ips) {
                    if (!Request::checkIp($ips)) {
                        throw new \App\Exceptions\HttpException(
                            403,
                            'ip not allowed,ip:' . Request::ip(),
                            null,
                            [],
                            \App\Consts\Errors::ERROR
                        );
                    }
                }
            )) {
                break;
            }
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
