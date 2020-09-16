<?php

return [
    'enabled' => boolval(intval(env('SESSION_ENABLED', false))),
    'save_handler' => env('SESSION_SAVE_HANDLER', 'files'),
    'save_path' => env('SESSION_SAVE_PATH', ROOT_PATH . '/storage/framework/sessions'),
];
