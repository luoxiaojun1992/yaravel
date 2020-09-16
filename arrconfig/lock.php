<?php

return [
    'key_prefix' => env('LOCK_KEY_PREFIX', 'lock:'),
    'redis_options' => [
        'connection' => env('LOCK_REDIS_CONNECTION', 'cache'),
    ]
];
