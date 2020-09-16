<?php

namespace App\Services\Providers;

use Illuminate\Container\Container;

/**
 * Class AbstractLaravelProvider
 *
 * 和laravel相关的包的provider
 *
 * @package App\Services\Providers
 */
abstract class AbstractLaravelProvider extends AbstractProvider
{
    /** @var Container */
    protected $app;

    /** @var Container 别名，方便理解 */
    protected $laravelDI;

    public function init()
    {
        $this->laravelDI = $this->app = $this->registry->get('services.di');
    }
}
