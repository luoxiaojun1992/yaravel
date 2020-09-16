<?php

return [
    'cross_domains' => env('AUTH_CROSS_DOMAINS', ''),

    'ip_restriction' => [
        [
            'api_prefix' => env('AUTH_IP_RES_API_PREFIX', ''),
            'ips' => env('AUTH_IP_RES_IPS', ''),
        ],
    ],
];
