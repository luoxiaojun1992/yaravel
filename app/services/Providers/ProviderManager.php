<?php

namespace App\Services\Providers;

use App\Services\Optimizer;
use App\Services\Register;
use Yaf\Dispatcher;

class ProviderManager
{
    protected static $providers = [];

    public static function init()
    {
        if (!is_null($providersCache = Optimizer::providersConfigCache())) {
            static::$providers = $providersCache;
        } else {
            static::$providers = static::providers();
        }
    }

    /**
     * @param null|Dispatcher $dispatcher
     */
    public static function register($dispatcher = null)
    {
        /** @var Register $registry */
        $registry = \Registry::get('services.register');
        foreach (static::$providers as $providers) {
            foreach ($providers as $providerInfo) {
                $providerClass = $providerInfo['class'];

                if (!class_exists($providerClass)) {
                    continue;
                }

                if ($providerInfo['is_deferred']) {
                    \App\Support\Collection::make($providerInfo['provides'])->map(function ($res) use (
                        $providerClass, $registry
                    ) {
                        $registry->addDeferProvider($res, $providerClass);
                    });
                } else {
                    /** @var \App\Services\Providers\AbstractProvider $provider */
                    $provider = new $providerClass($registry, $dispatcher);
                    $provider->register();
                }
            }
        }
    }

    protected static function excludedComposerProviders()
    {
        $composerJsonPath = ROOT_PATH . '/composer.json';
        if (file_exists($composerJsonPath)) {
            $composerJson = file_get_contents($composerJsonPath);
            if ($composerJson) {
                $composerConfig = json_decode($composerJson, true);
                if (isset($composerConfig['extra']['yaravel']['excluded'])) {
                    return $composerConfig['extra']['yaravel']['excluded'];
                }
            }
        }

        return [];
    }

    /**
     * @return array
     */
    protected static function composerProviders()
    {
        $providers = [];

        $installedJsonPath = ROOT_PATH . '/vendor/composer/installed.json';
        if (file_exists($installedJsonPath)) {
            $installedJson = file_get_contents($installedJsonPath);
            if ($installedJson) {
                $dependencies = json_decode($installedJson, true);
                if (isset($dependencies['packages'])) {
                    $dependencies = $dependencies['packages'];
                }
                $excludedProviders = static::excludedComposerProviders();
                foreach ($dependencies as $dependency) {
                    if (isset($dependency['extra']['yaravel']['providers'])) {
                        $composerProviders = array_diff(
                            $dependency['extra']['yaravel']['providers'],
                            $excludedProviders
                        );
                        $providers = array_merge(
                            $providers,
                            $composerProviders
                        );
                    }
                }
            }
        }

        return $providers;
    }

    /**
     * 优先执行core providers，保证composer providers能使用核心资源
     *
     * @param $configProviders
     * @param $composerProviders
     * @return array
     */
    public static function mergeProviders($configProviders, $composerProviders)
    {
        return [$configProviders, $composerProviders];
    }

    protected static function parseProviders($allProviders)
    {
        $allProvidersInfo = [];

        $registry = \Registry::get('services.register');
        foreach ($allProviders as $providers) {
            $providersInfo = [];

            foreach ($providers as $providerClass) {
                $providerInfo = [];

                /** @var \App\Services\Providers\AbstractProvider $provider */
                $provider = new $providerClass($registry);

                $providerInfo['is_deferred'] = $provider->isDeferred();
                $providerInfo['provides'] = $provider->provides();
                $providerInfo['class'] = $providerClass;

                $providersInfo[] = $providerInfo;
            }

            $allProvidersInfo[] = $providersInfo;
        }

        return $allProvidersInfo;
    }

    public static function providers()
    {
        $configProviders = \Config::get('app')['providers'];
        $composerProviders = static::composerProviders();
        $providers = static::mergeProviders($configProviders, $composerProviders);
        return static::parseProviders($providers);
    }

    /**
     * @return array
     * @throws \App\Exceptions\ErrorException
     */
    public static function underlyingProviders()
    {
        $config = \Config::fetchUnderlyingMergedConfig();
        $configProviders = $config->get('app')['providers'];
        $composerProviders = static::composerProviders();
        $providers = static::mergeProviders($configProviders, $composerProviders);
        return static::parseProviders($providers);
    }
}
