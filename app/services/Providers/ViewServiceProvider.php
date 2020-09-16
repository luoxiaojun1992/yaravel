<?php

namespace App\Services\Providers;

use League\Plates\Engine;

class ViewServiceProvider extends AbstractProvider
{
    protected $defer = true;

    public function register()
    {
        class_exists('League\Plates\Engine') && $this->registerPlates();
    }

    /**
     * Register Plates Engine.
     */
    public function registerPlates()
    {
        $plates = new Engine(APP_PATH.'/views');

        \Registry::alias('services.view', $plates);
    }

    public function provides()
    {
        return ['services.view'];
    }
}
