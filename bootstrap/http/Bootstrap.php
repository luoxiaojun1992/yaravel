<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Dotenv\Dotenv;
use Yaf\Bootstrap_Abstract as YafBootstrap;
use Yaf\Dispatcher;
use Yaf\Request_Abstract as YafRequest;
use \App\Services\Register;

/**
 * Class Bootstrap.
 *
 * @author overtrue <i@overtrue.me>
 */
class Bootstrap extends YafBootstrap
{
    /**
     * @var Register
     */
    protected $register;

    /**
     * 用于laravel相关的包内部获取配置或者某些依赖的资源
     *
     * @var \Illuminate\Container\Container
     */
    protected $laravelDI;

    /**
     * 项目基本初始化操作.
     *
     * @param Dispatcher $dispatcher
     */
    public function _initProject(Dispatcher $dispatcher)
    {
        $dispatcher->returnResponse(true);
        $dispatcher->disableView();
    }

    /**
     * autoload.
     *
     * @param Dispatcher $dispatcher [description]
     */
    public function _initLoader(Dispatcher $dispatcher)
    {
        $loader = \Yaf\Loader::getInstance();
        $loader->import(ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
    }

    /**
     * optimization.
     *
     * @param Dispatcher $dispatcher [description]
     */
    public function _initOptimization(Dispatcher $dispatcher)
    {
        \App\Services\Optimizer::providerCache();
    }

    /**
     * init container
     * @param Dispatcher $dispatcher
     */
    public function _initContainer(Dispatcher $dispatcher)
    {
        // init laravel container
        $this->laravelDI = new \App\Services\Laravel\Container();
        \Illuminate\Container\Container::setInstance($this->laravelDI);

        // init Container
        $this->register = $register = new Register();

        // 交叉引用，not useful for swoole
        $this->laravelDI->setRegister($register);

        $this->laravelDI->instance('path.lang', ROOT_PATH.DIRECTORY_SEPARATOR.'/resources/lang');
        $this->register->set(Register::class, $register);
        $this->register->alias('services.register', $register);
        $this->register->alias('services.di', $this->laravelDI);
    }

    public function _initDispatcherContext(Dispatcher $dispatcher)
    {
        $this->register->alias('context.http.dispatcher', $dispatcher);
    }

    /**
     * init Facade
     *
     * @param Dispatcher $dispatcher
     */
    public function _initFacade(Dispatcher $dispatcher)
    {
        // inject Register Container
        Facade::init($this->register);
    }

    public function _initEnv(Dispatcher $dispatcher)
    {
        (new Dotenv(ROOT_PATH))->load();
    }

    /**
     * init ini config and array config
     *
     * @param \Yaf\Dispatcher $dispatcher
     *
     * @throws \App\Exceptions\ErrorException
     */
    public function _initConfig(Dispatcher $dispatcher)
    {
        Config::init();
    }

    /**
     * init request context
     * @param Dispatcher $dispatcher
     */
    public function _initRequestContext(Dispatcher $dispatcher)
    {
        /** @var YafRequest $yafRequest */
        $yafRequest = $dispatcher->getRequest();
        if ($yafRequest) {
            $request = \App\Services\Http\Request::createFromYafRequest($yafRequest);
            $this->register->alias('context.http.request', $request);
        }
    }

    /**
     * 初始化时区
     *
     * @param Dispatcher $dispatcher
     */
    public function _initTimezone(Dispatcher $dispatcher)
    {
        date_default_timezone_set(Config::get('app')['timezone']);
    }

    public function _initProviders(Dispatcher $dispatcher)
    {
        \App\Services\Providers\ProviderManager::init();
        \App\Services\Providers\ProviderManager::register($dispatcher);
    }

    /**
     * 注册插件.
     *
     * @param Dispatcher $dispatcher
     */
    public function _initPlugins(Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new RequestPlugin());

        $appConfig = Config::get('app');

        if ($appConfig['env'] === 'dev') {
            if ($appConfig['debug']) {
                $dispatcher->registerPlugin(new DevHelperPlugin());
            } else {
                $dispatcher->registerPlugin(new ExceptionHandlerPlugin());
            }
        } else {
            $dispatcher->registerPlugin(new ExceptionHandlerPlugin());
        }

        $dispatcher->registerPlugin(new QueryLogTracerPlugin());
        $dispatcher->registerPlugin(new SessionPlugin());
        $dispatcher->registerPlugin(new ZipkinPlugin());
        $dispatcher->registerPlugin(new IpRestrictionPlugin());
        $dispatcher->registerPlugin(new SetHeaderPlugin());
        $dispatcher->registerPlugin(new CrossRequestPlugin());
        $dispatcher->registerPlugin(new ThrottlePlugin());

        //Tips: how to register new plugin
//        $dispatcher->registerPlugin(new SamplePlugin);
//        ...
    }
}
