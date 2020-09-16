<?php

return [
    'config' => [
        'enabled' => boolval(intval(env('OPTIMIZER_CONFIG_ENABLED', true))),
    ],
    'laravel_config' => [
        'enabled' => boolval(intval(env('OPTIMIZER_LARAVEL_CONFIG_ENABLED', true))),
    ],
    'providers_config' => [
        'enabled' => boolval(intval(env('OPTIMIZER_PROVIDERS_CONFIG_ENABLED', true))),
    ],
    'providers' => [
        'enabled' => boolval(intval(env('OPTIMIZER_PROVIDERS_ENABLED', false))),
    ],
];
