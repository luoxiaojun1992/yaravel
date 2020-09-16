<?php
/**
 * Created by PhpStorm.
 * User: reatang
 * Date: 18/4/10
 * Time: 下午11:29
 */

namespace App\Services;

use App\Support\Collection;
use App\Support\Traits\PhpSapi;
use App\Support\Traits\Reflection;
use Illuminate\Container\Container;
use Psr\Container\ContainerInterface;
use Yaf\Registry as YafRegister;

/**
 * Register Container
 *
 * 属性container引用了laravel container，laravel container也引用了registry
 *
 * @package App\Services
 */
class Register implements ContainerInterface
{
    use PhpSapi;
    use Reflection;

    /** @var Container */
    protected $container;

    protected $deferProviders = [];

    protected $loadedDeferProviders = [];

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    public function addDeferProvider($name, $providerClass)
    {
        $this->deferProviders[$name][] = $providerClass;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasDeferProvider($name)
    {
        return isset($this->deferProviders[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function registerDeferProvider($name)
    {
        if (isset($this->deferProviders[$name])) {
            if ($this->isCli()) {
                $dispatcher = null;
            } else {
                $dispatcher = $this->get('context.http.dispatcher');
            }
            $providers = $this->deferProviders[$name];
            unset($this->deferProviders[$name]);
            Collection::make($providers)->map(function ($providerClass) use ($dispatcher) {
                if (!class_exists($providerClass)) {
                    return;
                }
                if (isset($this->loadedDeferProviders[$providerClass])) {
                    return;
                }

                $this->loadedDeferProviders[$providerClass] = true;

                /** @var \App\Services\Providers\AbstractProvider $provider */
                $provider = new $providerClass($this, $dispatcher);
                $provider->register();
            });
            return true;
        }

        return false;
    }

    /**
     * set instance
     *
     * @param $name
     * @param $instance
     *
     * @return $this
     */
    public function set($name, $instance)
    {
        if (is_null($this->container)) {
            YafRegister::set($name, $instance);
        } else {
            $this->container->instance($name, $instance);
        }

        return $this;
    }

    /**
     * alias name
     *
     * @param $name
     * @param $instance
     *
     * @return $this
     */
    public function alias($name, $instance)
    {
        return $this->set($name, $instance);
    }

    public function _get($name, $instanceArgs = [])
    {
        if (is_null($this->container)) {
            $res = YafRegister::get($name);
            if (is_null($res) || ($res === false)) {
                $res = YafRegister::get('services.' . $name);
                if (is_null($res) || ($res === false)) {
                    /** @var Container $di */
                    $di = YafRegister::get('services.di');
                    if ($di) {
                        if ($di->has($name)) {
                            $res = $di->make($name);
                        }
                    }
                }
            }
        } else {
            if ($this->container->has($name)) {
                $res = $this->container->make($name);
            } else {
                $res = null;
            }
        }

        return $res;
    }

    /**
     * get instance
     *
     * @param string $name
     * @param array $instanceArgs
     *
     * @return mixed
     */
    public function get($name, $instanceArgs = [])
    {
        if ($this->hasDeferProvider($name)) {
            $this->registerDeferProvider($name);
        }

        $res = $this->_get($name, $instanceArgs);

        if (!$res) {
            if ($this->isInstantiableClass($name)) {
                if (count($instanceArgs) > 0) {
                    $res = new $name(...$instanceArgs);
                } else {
                    $res = new $name;
                }
            }
        }

        return $res;
    }

    protected function _has($name)
    {
        if (is_null($this->container)) {
            $res = YafRegister::has($name);
            if ($res === false) {
                $res = YafRegister::has('services.' . $name);
                if ($res === false) {
                    /** @var Container $di */
                    $di = YafRegister::get('services.di');
                    if ($di !== false) {
                        $res = $di->has($name);
                    }
                }
            }
        } else {
            $res = $this->container->has($name);
        }

        return $res;
    }

    /**
     * instance isset
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        if ($this->hasDeferProvider($name)) {
            return true;
        }

        $res = $this->_has($name);

        if (!$res) {
            $res = $this->isInstantiableClass($name);
        }

        return $res;
    }
}
