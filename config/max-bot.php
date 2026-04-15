<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | MAX Bot API Token
    |--------------------------------------------------------------------------
    |
    | Your MAX messenger bot token. Get it from @MaxBotFather in MAX.
    |
    */
    'token' => env('MAX_BOT_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    */
    'http' => [
        'base_uri' => env('MAX_BOT_BASE_URI', 'https://platform-api.max.ru'),
        'timeout' => (int) env('MAX_BOT_TIMEOUT', 30),
        'retry' => [
            'times' => 3,
            'sleep' => 100, // milliseconds base for exponential backoff
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'secret' => env('MAX_BOT_WEBHOOK_SECRET', null),
        'version' => env('MAX_BOT_WEBHOOK_VERSION', null),
        'route' => [
            'enabled' => true,
            'path' => env('MAX_BOT_WEBHOOK_PATH', 'max-bot/webhook'),
            'middleware' => ['api'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => (bool) env('MAX_BOT_QUEUE_ENABLED', false),
        'connection' => env('MAX_BOT_QUEUE_CONNECTION', null),
        'queue' => env('MAX_BOT_QUEUE_NAME', 'default'),
    ],
];
