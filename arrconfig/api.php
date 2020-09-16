<?php

return [
    //统一标识微服务名称
    'service_name' => env('API_SERVICE_NAME', 'api-jstracking'),

    'v2_domain' => env('API_V2_DOMAIN', 'https://devapi.jingsocial.com'),

    'v2_internal_domain' => env('API_V2_INTERNAL_DOMAIN', 'http://dev.callback.jingsocial.com'),

    'v2_monitor_domain' => env('API_V2_MONITOR_DOMAIN', 'http://jms-jing-monitor-backend-svc'),
];
