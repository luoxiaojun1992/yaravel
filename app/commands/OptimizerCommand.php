<?php

namespace App\Commands;

use App\Services\Optimizer;
use Symfony\Component\Console\Input\InputOption;

/**
 * Optimizer command.
 *
 * @example php sora optimizer 执行所有优化
 * @example php sora optimizer --target config 执行配置缓存优化
 * @example php sora optimizer --target provider 执行provider缓存优化
 * @example php sora optimizer --target clear 清除所有优化
 * @example php sora optimizer --target clear_config 清除配置缓存优化
 * @example php sora optimizer --target clear_provider 清除provider缓存优化
 */
class OptimizerCommand extends Command
{
    protected $name = 'optimizer';
    protected $description = '性能优化：缓存';

    protected function configure()
    {
        $this->addOption(
            'target',
            'target',
            InputOption::VALUE_OPTIONAL,
            '优化对象',
            'all'
        );
    }

    public function handle()
    {
        $target = $this->option('target');
        switch ($target) {
            case 'all':
                $this->info('Framework: optimizing all.');
                Optimizer::all();
                $this->info('Framework: all optimized.');
                break;
            case 'config':
                $this->info('Framework: caching config.');
                Optimizer::cacheConfig();
                $this->info('Framework: config cached.');

                $this->info('Framework: caching laravel config.');
                Optimizer::cacheLaravelConfig();
                $this->info('Framework: laravel config cached.');

                $this->info('Framework: caching providers config.');
                Optimizer::cacheProvidersConfig();
                $this->info('Framework: providers config cached.');
                break;
            case 'provider':
                $this->info('Framework: caching providers.');
                Optimizer::cacheProvider();
                $this->info('Framework: providers cached.');
                break;
            case 'clear':
                $this->info('Framework: cleaning all.');
                Optimizer::clear();
                $this->info('Framework: all cleaned.');
                break;
            case 'clear_config':
                $this->info('Framework: cleaning config.');
                Optimizer::clearConfigCache();
                Optimizer::clearLaravelConfigCache();
                Optimizer::clearProvidersConfigCache();
                $this->info('Framework: config cleaned.');
                break;
            case 'clear_provider':
                $this->info('Framework: cleaning providers.');
                Optimizer::clearProviderCache();
                $this->info('Framework: providers cleaned.');
                break;
        }
    }
}
