<?php

namespace App\Services\Elasticsearch;

use App\Services\Providers\AbstractLaravelProvider;
use Cviebrock\LaravelElasticsearch\Factory;
use Elasticsearch\Client;


/**
 * Class ServiceProvider
 *
 * @package App\Services\Providers
 */
class ElasticSearchProvider extends AbstractLaravelProvider
{
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $app->singleton('elasticsearch.tracer', function($app){
            $tracer = new Tracer();
            $this->registry->alias('services.elasticsearch.tracer', $tracer);
            return $tracer;
        });

        $app->singleton('elasticsearch.factory', function($app) {
            return new Factory();
        });

        $app->singleton('elasticsearch', function($app) {
            $manager = new Manager($app, $app['elasticsearch.factory']);
            $this->registry->alias('services.elasticsearch', $manager);
            return $manager;
        });

        $app->alias('elasticsearch', Manager::class);

        $app->singleton(Client::class, function($app) {
            return $app['elasticsearch']->connection();
        });
    }

    public function provides()
    {
        return [
            'elasticsearch.tracer', 'elasticsearch.factory', 'elasticsearch',
            Manager::class, Client::class,
        ];
    }
}
