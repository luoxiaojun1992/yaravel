<?php

namespace App\Services\Providers;

use Carbon\Carbon;
use Illuminate\Events\Dispatcher;
use Illuminate\Translation\Translator as IlluminateTranslator;
use Symfony\Component\Translation\Translator;

class CarbonServiceProvider extends AbstractLaravelProvider
{
    public function register()
    {
        $service = $this;
        $events = $this->app['events'];
        if ($events instanceof Dispatcher) {
            $events->listen(class_exists('App\Events\LocaleUpdated') ? 'App\Events\LocaleUpdated' : 'locale.changed', function () use ($service) {
                $service->updateLocale();
            });
            $service->updateLocale();
        }
    }

    public function updateLocale()
    {
        $translator = $this->app['translator'];
        if ($translator instanceof Translator || $translator instanceof IlluminateTranslator) {
            Carbon::setLocale($translator->getLocale());
        }
    }
}
