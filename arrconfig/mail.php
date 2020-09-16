<?php

return [
    'default_channel' => env('MAIL_DEFAULT_CHANNEL', 'default'),
    'channels' => [
        'default' => [
            'host' => env('MAIL_DEFAULT_HOST', 'localhost'),
            'port' => env('MAIL_DEFAULT_PORT', 25),
            'encryption' => env('MAIL_DEFAULT_ENCRYPTION', null),
            'username' => env('MAIL_DEFAULT_USERNAME'),
            'password' => env('MAIL_DEFAULT_PASSWORD'),
        ],
    ],
];
