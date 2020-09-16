<?php

use Dotenv\Dotenv;
use \App\Services\Register;

/**
 * Class Bootstrap.
 *
 * @author overtrue <i@overtrue.me>
 */
class Bootstrap
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

    public function init()
    {
        $this->initOptimization();
        $this->initContainer();
        $this->initFacade();
        $this->initEnv();
        $this->initConfig();
        $this->initTimezone();
        $this->initProviders();
    }

    /**
     * optimization.
     */
    public function initOptimization()
    {
        \App\Services\Optimizer::providerCache();
    }

    /**
     * init container
     */
    public function initContainer()
    {
        // init laravel container
        $this->laravelDI = new \App\Services\Laravel\Container();
        \Illuminate\Container\Container::setInstance($this->laravelDI);

        // init Container
        $this->register = $register = new Register($this->laravelDI);

        $this->laravelDI->setRegister($register);

        $this->laravelDI->instance('path.lang', ROOT_PATH.DIRECTORY_SEPARATOR.'/resources/lang');
        $this->register->set(Register::class, $register);
        $this->register->alias('services.register', $register);
        $this->register->alias('services.di', $this->laravelDI);
    }

    /**
     * init Facade
     */
    public function initFacade()
    {
        // inject Register Container
        Facade::init($this->register);
    }

    public function initEnv()
    {
        (new Dotenv(ROOT_PATH))->load();
    }

    /**
     * init ini config and array config
     *
     * @throws \App\Exceptions\ErrorException
     */
    public function initConfig()
    {
        Config::init();
    }

    /**
     * 初始化时区
     */
    public function initTimezone()
    {
        date_default_timezone_set(Config::get('app')['timezone']);
    }

    public function initProviders()
    {
        \App\Services\Providers\ProviderManager::init();
        \App\Services\Providers\ProviderManager::register();
    }
}
