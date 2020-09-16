<?php

namespace App\Services\Mail;

use App\Services\Providers\AbstractProvider;

class MailServiceProvider extends AbstractProvider
{
    protected $defer = true;

    public function register()
    {
        $config = \Config::get('mail');

        $mailManager = (new Manager())->setDefaultChannel($config['default_channel'])
            ->setChannelsConfig($config['channels']);

        $this->registry->alias('services.mail', $mailManager);
    }

    public function provides()
    {
        return ['services.mail'];
    }
}
