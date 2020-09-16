<?php

namespace App\Services\Providers;

use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;

class ValidationServiceProvider extends AbstractLaravelProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPresenceVerifier();

        $this->registerValidationFactory();
    }

    /**
     * Register the validation factory.
     *
     * @return void
     */
    protected function registerValidationFactory()
    {
        $this->app->singleton('validator', function ($app) {
            $validator = new Factory($app['translator'], $app);

            // The validation presence verifier is responsible for determining the existence of
            // values in a given data collection which is typically a relational database or
            // other persistent data stores. It is used to check for "uniqueness" as well.
            if (isset($app['db'], $app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            $validator->extend('callable', function ($attribute, $value, $parameters, $validator) {
                return call_user_func_array($parameters, [
                    'attribute' => $attribute,
                    'value' => $value,
                    'data' => $validator->getData(),
                    'validator' => $validator,
                    'errors' => $validator->errors(),
                ]);
            });

            $this->registry->alias('services.validator', $validator);

            return $validator;
        });

        $this->app->alias('validator', \Illuminate\Validation\Factory::class);
        $this->app->alias('validator', \Illuminate\Contracts\Validation\Factory::class);
    }

    /**
     * Register the database presence verifier.
     *
     * @return void
     */
    protected function registerPresenceVerifier()
    {
        $this->app->singleton('validation.presence', function ($app) {
            return new DatabasePresenceVerifier($app['db']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'validator', 'validation.presence', \Illuminate\Validation\Factory::class,
            \Illuminate\Contracts\Validation\Factory::class,
        ];
    }
}
