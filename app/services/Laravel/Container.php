<?php

namespace App\Services\Laravel;

use App\Events\LocaleUpdated;
use App\Exceptions\HttpException;
use App\Services\Register;
use App\Support\Traits\PhpSapi;

/**
 * Class Container
 *
 * 属性register引用了registry，registry也引用了laravel container
 *
 * @package App\Services\Laravel
 */
class Container extends \Illuminate\Container\Container
{
    use PhpSapi;

    /**
     * The application namespace.
     *
     * @var string
     */
    protected $namespace;

    /** @var Register */
    protected $register;

    /**
     * @param Register $register
     * @return $this
     */
    public function setRegister($register)
    {
        $this->register = $register;
        return $this;
    }

    /**
     * Resolve the given type from the container.
     *
     * (Overriding Container::make)
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if ($this->register->hasDeferProvider($abstract) && ! isset($this->instances[$abstract])) {
            $this->register->registerDeferProvider($abstract);
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * (Overriding Container::bound)
     *
     * @param  string  $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return $this->register->hasDeferProvider($abstract) || parent::bound($abstract);
    }

    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return \Yaf\VERSION;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath()
    {
        return ROOT_PATH;
    }

    /**
     * Get or check the current application environment.
     *
     * @return string
     */
    public function environment()
    {
        return \Config::get('app')['env'];
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return $this->isCli();
    }

    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);

        $this['translator']->setLocale($locale);

        $this['events']->dispatch(new LocaleUpdated($locale));
    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws HttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents(ROOT_PATH . '/composer.json'), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath(APP_PATH) == realpath(ROOT_PATH.'/'.$pathChoice)) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new \RuntimeException('Unable to detect application namespace.');
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Determine if application locale is the given locale.
     *
     * @param  string  $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }
}
