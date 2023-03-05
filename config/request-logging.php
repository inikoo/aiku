<?php

return [

    'enabled' => env('REQUEST_LOGGING_ENABLED', false),

    'methods' => [
        'GET',
        'POST',
        'PATCH',
        'PUT',
        'DELETE',
    ],

    'exclude-routes' => [
        'api/iris/*'
    ],

    'exclude-request-fields' => [
        'password',
        'password_confirmation',
    ],

    'request-duration-limit' => false,
    'show-response-html'     => false,

    'exclude-response-fields' => [
    ],

    'log-channels' => [
        'stack'
    ],
    'log-level' => 'info',

    'warning-log-channels' => [
        'stack'
    ],
    'warning-log-level' => 'warning',

    'database-logging' => [
        'enabled'        => true,
        'table'          => 'requests',
        'persistence'    => 7,
        'limit-response' => 2000,
    ],
];
