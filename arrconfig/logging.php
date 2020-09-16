<?php

return [
    'default_channel' => env('LOG_DEFAULT_CHANNEL', 'default'),

    //可定义不同的log类型，比如web log、command log
    'channels' => [
        'default' => [
            //
        ],
        'defer' => [
            'is_defer' => boolval(intval(env('LOG_DEFER_IS_DEFER', true))),
        ],
    ],
];
