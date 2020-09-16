<?php

namespace App\Services\Providers;

use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;

class FilesystemServiceProvider extends AbstractLaravelProvider
{
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerNativeFilesystem();

        $this->registerFlysystem();
    }

    /**
     * Register the native filesystem implementation.
     *
     * @return void
     */
    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', function () {
            return new Filesystem();
        });
        $this->app->alias('files', Filesystem::class);
    }

    /**
     * Register the driver based filesystem.
     *
     * @return void
     */
    protected function registerFlysystem()
    {
        $this->registerManager();

        $this->app->singleton('filesystem.disk', function () {
            return $this->app['filesystem']->disk($this->getDefaultDriver());
        });

        $this->app->alias('filesystem.disk', \Illuminate\Contracts\Filesystem\Filesystem::class);

        $this->app->singleton('filesystem.cloud', function () {
            return $this->app['filesystem']->disk($this->getCloudDriver());
        });

        $this->app->alias('filesystem.cloud', Cloud::class);
    }

    /**
     * Register the filesystem manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('filesystem', function () {
            $storage = new FilesystemManager($this->app);
            $this->registry->alias('services.filesystem', $storage);
            return $storage;
        });
        $this->app->alias('filesystem', FilesystemManager::class);
        $this->app->alias('filesystem', Factory::class);
    }

    /**
     * Get the default file driver.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }

    /**
     * Get the default cloud based file driver.
     *
     * @return string
     */
    protected function getCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }

    public function provides()
    {
        return [
            'files', 'filesystem.disk', 'filesystem.cloud',
            'filesystem', Filesystem::class, FilesystemManager::class,
            Factory::class, \Illuminate\Contracts\Filesystem\Filesystem::class,
            Cloud::class,
        ];
    }
}
