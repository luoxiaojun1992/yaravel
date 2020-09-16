<?php

return [
    'service_name' => env('ZIPKIN_SERVICE_NAME', 'yaravel'),
    'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
    'sample_rate' => doubleval(env('ZIPKIN_SAMPLE_RATE', 0)),
    'body_size' => intval(env('ZIPKIN_BODY_SIZE', 500)), //记录http body长度，单位字节
    'curl_timeout' => intval(env('ZIPKIN_CURL_TIMEOUT', 1)), //超时时间，单位秒
    'redis_options' => [
        'queue_name' => env('ZIPKIN_QUEUE_NAME', 'queue:zipkin:span'),
        'connection' => env('ZIPKIN_REDIS_CONNECTION', 'zipkin'),
    ],
    'report_type' => env('ZIPKIN_REPORT_TYPE', 'http'),
];
