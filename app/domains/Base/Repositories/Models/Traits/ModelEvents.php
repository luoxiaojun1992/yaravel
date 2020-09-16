<?php

namespace App\Domains\Base\Repositories\Models\Traits;

trait ModelEvents
{
    use ModelValidator;

    /**
     * Each model class boots only once
     */
    protected static function bootModelEvents()
    {
        static::creating(function($model) {
            if (method_exists($model, 'beforeCreate')) {
                return call_user_func([$model, 'beforeCreate']);
            }

            return null;
        });
        static::created(function($model) {
            if (method_exists($model, 'afterCreate')) {
                return call_user_func([$model, 'afterCreate']);
            }

            return null;
        });
        static::updating(function($model) {
            if (method_exists($model, 'beforeUpdate')) {
                return call_user_func([$model, 'beforeUpdate']);
            }

            return null;
        });
        static::updated(function($model) {
            if (method_exists($model, 'afterUpdate')) {
                return call_user_func([$model, 'afterUpdate']);
            }

            return null;
        });
        static::deleting(function($model) {
            if (method_exists($model, 'beforeDelete')) {
                return call_user_func([$model, 'beforeDelete']);
            }

            return null;
        });
        static::deleted(function($model) {
            if (method_exists($model, 'afterDelete')) {
                return call_user_func([$model, 'afterDelete']);
            }

            return null;
        });
        static::saving(function($model) {
            if (method_exists($model, 'beforeSave')) {
                return call_user_func([$model, 'beforeSave']);
            }

            return null;
        });
        static::saved(function($model) {
            if (method_exists($model, 'afterSave')) {
                return call_user_func([$model, 'afterSave']);
            }

            return null;
        });
        static::validating(function($model) {
            if (method_exists($model, 'beforeValidate')) {
                return call_user_func([$model, 'beforeValidate']);
            }

            return null;
        });
        static::validated(function($model) {
            if (method_exists($model, 'afterValidate')) {
                return call_user_func([$model, 'afterValidate']);
            }

            return null;
        });
    }

    /**
     * Register a validating model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function validating($callback)
    {
        static::registerModelEvent('validating', $callback);
    }

    /**
     * Register a validated model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function validated($callback)
    {
        static::registerModelEvent('validated', $callback);
    }

    /**
     * Before insert model data
     *
     * @param bool $validate
     * @throws \Exception
     * @return bool|null
     */
    protected function beforeCreate($validate = true)
    {
        if ($validate) {
            return $this->validate();
        }

        return null;
    }

    /**
     * Before save model data
     */
    protected function beforeSave()
    {
        //
    }

    /**
     * Before update model data
     *
     * @param bool $validate
     * @throws \Exception
     * @return bool|null
     */
    protected function beforeUpdate($validate = true)
    {
        if ($validate) {
            return $this->validate();
        }

        return null;
    }

    /**
     * Before delete model data
     */
    protected function beforeDelete()
    {
        //
    }

    /**
     * Before model data validation
     */
    protected function beforeValidate()
    {
        //
    }

    /**
     * After insert model data
     */
    protected function afterCreate()
    {
        //
    }

    /**
     * After save model data
     */
    protected function afterSave()
    {
        //
    }

    /**
     * After update model data
     */
    protected function afterUpdate()
    {
        //
    }

    /**
     * After delete model data
     */
    protected function afterDelete()
    {
        //
    }

    /**
     * After model data validation
     */
    protected function afterValidate()
    {
        //
    }
}
