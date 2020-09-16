<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Services\Config\Repository;

/**
 * class Config.
 *
 * @author overtrue <i@overtrue.me>
 */
class Config
{
    /**
     * @return Repository
     */
    protected static function getConfigContainer()
    {
        return Registry::get('services.config');
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public static function set($name, $value)
    {
        static::getConfigContainer()->set($name, $value);
    }

    /**
     * @param mixed $name
     * @param null   $default
     *
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        return static::getConfigContainer()->get($name, $default);
    }

    /**
     * @param mixed $name
     */
    public static function forget($name)
    {
        static::getConfigContainer()->forget($name);
    }

    /**
     * @param mixed $name
     *
     * @return bool
     */
    public static function has($name)
    {
        return static::getConfigContainer()->has($name);
    }

    /**
     * @return array
     */
    public static function all()
    {
        return static::getConfigContainer()->all();
    }

    /**
     * @param string $path
     * @return Repository
     * @throws \App\Exceptions\ErrorException
     */
    public static function fetchFromPath(string $path)
    {
        if (!is_dir($path) || !is_readable($path)) {
            abort('配置文件目录不存在');
        }

        $repository = new Repository();

        foreach (glob(rtrim($path, '/').'/*') as $file) {
            $repository->set(basename(str_replace('.ini', '', $file)), parse_ini_file($file));
        }

        return $repository;
    }

    /**
     * @param string $path
     * @throws \App\Exceptions\ErrorException
     */
    public static function createFromPath(string $path)
    {
        Registry::alias('services.config', static::fetchFromPath($path));
    }

    public static function createFromSnapshot($snapshot)
    {
        $repository = new Repository($snapshot);
        Registry::alias('services.config', $repository);
    }

    /**
     * @param Repository $repository
     * @param array $arrConfig
     */
    public static function mergeArrConfig($repository, $arrConfig)
    {
        foreach ($arrConfig as $configKey => $configValue) {
            $repository->set($configKey, $configValue);
        }
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws \App\Exceptions\ErrorException
     */
    public static function fetchFromPathOfArrConfig(string $path)
    {
        if (!is_dir($path) || !is_readable($path)) {
            abort('配置文件目录不存在');
        }

        $config = [];

        foreach (glob(rtrim($path, '/').'/*') as $file) {
            $configKey = basename(str_replace('.php', '', $file));
            $configValue = require $file;
            $config[$configKey] = $configValue;
        }

        return $config;
    }

    /**
     * @throws \App\Exceptions\ErrorException
     */
    public static function init()
    {
        $laravelDI = Registry::get('services.di');

        $laravelConfigCache = \App\Services\Optimizer::laravelConfigCache();
        if (is_null($laravelConfigCache)) {
            $arrConfig = static::fetchUnderlyingArrConfig();
        } else {
            $arrConfig = $laravelConfigCache;
        }
        $laravelDI['config'] = new \Illuminate\Config\Repository($arrConfig);
        $laravelDI->alias('config', \Illuminate\Config\Repository::class);
        $laravelDI->alias('config', \Illuminate\Contracts\Config\Repository::class);

        $configCache = \App\Services\Optimizer::configCache();
        if (is_null($configCache)) {
            $config = static::fetchUnderlyingConfig();
            static::mergeArrConfig($config, $arrConfig);
            Registry::alias('services.config', $config);
        } else {
            static::createFromSnapshot($configCache);
        }
    }

    /**
     * @return array
     * @throws \App\Exceptions\ErrorException
     */
    public static function fetchUnderlyingArrConfig()
    {
        return static::fetchFromPathOfArrConfig(ROOT_PATH . DIRECTORY_SEPARATOR . 'arrconfig');
    }

    /**
     * @return Repository
     * @throws \App\Exceptions\ErrorException
     */
    public static function fetchUnderlyingConfig()
    {
        return static::fetchFromPath(ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
    }

    /**
     * @return Repository
     * @throws \App\Exceptions\ErrorException
     */
    public static function fetchUnderlyingMergedConfig()
    {
        $config = static::fetchUnderlyingConfig();
        $arrConfig = static::fetchUnderlyingArrConfig();
        static::mergeArrConfig($config, $arrConfig);
        return $config;
    }
}
