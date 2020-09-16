<?php

namespace App\Services;

use App\Services\Providers\ProviderManager;
use App\Support\Str;

class Optimizer
{
    /**
     * @throws \App\Exceptions\ErrorException
     */
    public static function all()
    {
        static::cacheConfig();
        static::cacheLaravelConfig();
        static::cacheProvidersConfig();
        static::cacheProvider();
    }

    public static function clear()
    {
        static::clearConfigCache();
        static::clearLaravelConfigCache();
        static::clearProvidersConfigCache();
        static::clearProviderCache();
    }

    /**
     * @throws \App\Exceptions\ErrorException
     */
    public static function cacheConfig()
    {
        if (!\Config::get('optimizer')['config']['enabled']) {
            return;
        }

        $configSnapshot = \Config::fetchUnderlyingMergedConfig()->all();
        $configCache = '<?php' .
            str_repeat(PHP_EOL, 2) .
            'return ' . var_export($configSnapshot, true) . ';';
        file_put_contents(ROOT_PATH . '/storage/framework/cache/config/config.php', $configCache);
    }

    public static function clearConfigCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/config/config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    public static function configCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/config/config.php';

        if (file_exists($cachePath)) {
            return include $cachePath;
        }

        return null;
    }

    public static function cacheLaravelConfig()
    {
        if (!\Config::get('optimizer')['laravel_config']['enabled']) {
            return;
        }

        $configSnapshot = \Config::fetchUnderlyingArrConfig();
        $configCache = '<?php' .
            str_repeat(PHP_EOL, 2) .
            'return ' . var_export($configSnapshot, true) . ';';
        file_put_contents(ROOT_PATH . '/storage/framework/cache/config/laravel_config.php', $configCache);
    }

    public static function clearLaravelConfigCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/config/laravel_config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    public static function laravelConfigCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/config/laravel_config.php';

        if (file_exists($cachePath)) {
            return include $cachePath;
        }

        return null;
    }

    /**
     * @throws \App\Exceptions\ErrorException
     */
    public static function cacheProvidersConfig()
    {
        if (!\Config::get('optimizer')['providers_config']['enabled']) {
            return;
        }

        $configSnapshot = ProviderManager::underlyingProviders();
        $configCache = '<?php' .
            str_repeat(PHP_EOL, 2) .
            'return ' . var_export($configSnapshot, true) . ';';
        file_put_contents(ROOT_PATH . '/storage/framework/cache/config/providers_config.php', $configCache);
    }

    public static function clearProvidersConfigCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/config/providers_config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    public static function providersConfigCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/config/providers_config.php';

        if (file_exists($cachePath)) {
            return include $cachePath;
        }

        return null;
    }

    /**
     * @throws \App\Exceptions\ErrorException
     * @throws \ReflectionException
     */
    public static function cacheProvider()
    {
        if (!\Config::get('optimizer')['providers']['enabled']) {
            return;
        }

        $allProviders = ProviderManager::underlyingProviders();

        $providersCache = '';

        foreach ($allProviders as $providers) {
            foreach ($providers as $providerInfo) {
                $providerClassName = $providerInfo['class'];
                $reflectionClass = new \ReflectionClass($providerClassName);
                $classPath = $reflectionClass->getFileName();
                $classDir = realpath(dirname($classPath));
                $appProvidersDir = realpath(APP_PATH . '/services');
                if (Str::startsWith($classDir, $appProvidersDir, true)) {
                    $sourceCode = file_get_contents($classPath);
                    $sourceCode = trim($sourceCode);
                    $sourceCode = substr($sourceCode, strlen('<?php'));
                    $providersCache .= ('<?php' . PHP_EOL . $sourceCode . PHP_EOL . '?>' . PHP_EOL);
                }
            }
        }

        $providersCache = rtrim($providersCache, PHP_EOL);

        file_put_contents(ROOT_PATH . '/storage/framework/cache/class/providers.php', $providersCache);
    }

    public static function clearProviderCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/class/providers.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    public static function providerCache()
    {
        $cachePath = ROOT_PATH . '/storage/framework/cache/class/providers.php';

        if (file_exists($cachePath)) {
            include_once $cachePath;
        }
    }
}
