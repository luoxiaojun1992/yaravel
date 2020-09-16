<?php

use Yaf\Plugin_Abstract as YafPlugin;
use Yaf\Request_Abstract as YafRequest;
use Yaf\Response_Abstract as YafResponse;

/**
 * Class ThrottlePlugin
 *
 * @author overtrue <i@overtrue.me>
 */
class ThrottlePlugin extends YafPlugin
{
    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerStartup(YafRequest $request, YafResponse $response)
    {
        /* 在路由之前执行,这个钩子里，你可以做url重写等功能 */
        $config = Config::get('throttle');
        $apiPrefix = $config['api_prefix'];
        $throttleId = Request::route();

        Request::matchPath(array_column($apiPrefix, 'prefix'), false, function ($prefix, $i) use ($apiPrefix, $throttleId, $response) {
            $prefixConfig = $apiPrefix[$i];
            $throttle = $prefixConfig['throttle'];
            $pass = \App\Support\Throttle::pass($throttleId, $throttle, $prefixConfig['ttl']);
            if ($response) {
                $response->setHeader('X-RateLimit-Limit', (string)$throttle, false, null);
                $response->setHeader('X-RateLimit-Remaining', (string)\App\Support\Throttle::retriesLeft($throttleId, $throttle), false, null);
                $response->setHeader('X-RateLimit-Reset', (string)\App\Support\Throttle::availableIn($throttleId), false, null);
            } else {
                header('X-RateLimit-Limit:' . (string)$throttle, false, null);
                header('X-RateLimit-Remaining:' . (string)\App\Support\Throttle::retriesLeft($throttleId, $throttle), false, null);
                header('X-RateLimit-Reset:' . (string)\App\Support\Throttle::availableIn($throttleId), false, null);
            }

            if (!$pass) {
                throw new \App\Exceptions\HttpException(
                    429,
                    'too many requests',
                    null,
                    [],
                    \App\Consts\Errors::ERROR
                );
            }
        });
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
