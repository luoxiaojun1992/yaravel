<?php

namespace App\Services\Providers;

use App\Support\Traits\PhpSapi;

class SessionProvider extends AbstractProvider
{
    use PhpSapi;

    protected $defer = true;

    public function register()
    {
        if ($this->isCli()) {
            return;
        }

        $sessionConfig = \Config::get('session');

        if (!$sessionConfig['enabled']) {
            return;
        }

        ini_set('session.save_handler', $sessionConfig['save_handler']);
        session_save_path($sessionConfig['save_path']);
        $timeout = isset($sessionConfig['timeout']) ? intval($sessionConfig['timeout']) : 86400;
        ini_set('session.gc_maxlifetime', $timeout);
        ini_set('session.cache_limiter', $sessionConfig['cache_limiter'] ?? 'nocache');
        ini_set('session.cookie_httponly', $sessionConfig['cookie_httponly'] ?? '');
        ini_set('session.cookie_secure', $sessionConfig['cookie_secure'] ?? '');

        $session = \Yaf\Session::getInstance();

        $this->registry->alias('services.session', $session);
    }

    public function provides()
    {
        return ['services.session'];
    }
}
