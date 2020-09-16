<?php

namespace App\Services\Providers;

use App\Services\Register;
use Yaf\Dispatcher;

/**
 * Class AbstractProvider
 *
 * 通用的包的provider
 *
 * @package App\Services\Providers
 */
abstract class AbstractProvider
{
    protected $defer = false;

    /** @var Register  */
    protected $registry;

    /** @var Dispatcher */
    protected $dispatcher;

    public function __construct(Register $register, $dispatcher = null)
    {
        //For debug, don't use
//        var_dump('Loaded provider:' . static::class);

        $this->registry = $register;
        $this->dispatcher = $dispatcher;

        $this->init();
    }

    /**
     * 留给子类初始化使用，避免直接调用构造函数，不需要调用父类的构造函数
     */
    public function init()
    {
        //
    }

    public function provides()
    {
        return [];
    }

    /**
     * Determine if the provider is deferred.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->defer;
    }

    abstract public function register();
}
