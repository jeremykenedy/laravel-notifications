<?php

return [
    'enabled'                => env('NOTIFICATIONS_ENABLED', true),
    'per_page'               => env('NOTIFICATIONS_PER_PAGE', 20),
    'auto_mark_read_on_view' => env('NOTIFICATIONS_AUTO_READ', false),

    'bell' => [
        'show_count'        => true,
        'max_count_display' => 99,
        'poll_interval_ms'  => env('NOTIFICATIONS_POLL_MS', 30000),
    ],

    'routes' => [
        'enabled'    => true,
        'prefix'     => 'notifications',
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages
    |--------------------------------------------------------------------------
    | When enabled, success/error flash messages are shown inline on the
    | notification pages. Disable this if you use a toast package to
    | avoid duplicate feedback (toast + inline alert).
    |
    */

    'flash_messages' => env('NOTIFICATIONS_FLASH_MESSAGES', false),

    /*
    |--------------------------------------------------------------------------
    | Confirm Style
    |--------------------------------------------------------------------------
    | Controls how destructive actions (delete) are confirmed.
    | 'modal' = in-app confirmation modal (recommended)
    | 'browser' = native browser confirm() dialog
    |
    */

    'confirm_style' => env('NOTIFICATIONS_CONFIRM_STYLE', 'modal'),

    /*
    |--------------------------------------------------------------------------
    | Send Notification GUI
    |--------------------------------------------------------------------------
    | The send notification page allows authorized users to compose and
    | broadcast notifications to all users or specific roles.
    |
    */

    'send' => [
        'enabled'    => env('NOTIFICATIONS_SEND_ENABLED', true),
        'middleware' => ['web', 'auth', 'verified', 'level:5'],
    ],

    'user_model' => env('NOTIFICATIONS_USER_MODEL', 'App\\Models\\User'),
    'role_model' => env('NOTIFICATIONS_ROLE_MODEL', 'App\\Models\\Role'),

    'broadcast' => [
        'enabled' => env('NOTIFICATIONS_BROADCAST_ENABLED', true),
    ],
];
