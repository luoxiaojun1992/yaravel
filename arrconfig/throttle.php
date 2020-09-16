<?php

return [
    'cache' => [
        'driver' => env('THROTTLE_CACHE_DRIVER', 'throttle'),
        'key_prefix' => env('THROTTLE_CACHE_KEY_PREFIX', 'throttle:'),
    ],
    'api_prefix' => [
        [
            'prefix' => '/',
            'throttle' => 10000,
            'ttl' => 1,
        ]
    ],
];
