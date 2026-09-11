<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Frameworks
    |--------------------------------------------------------------------------
    | Which set of views this package renders. When either value is left empty
    | the matching laravel-ui-kit setting is used, so an application already
    | driven by UI_KIT_CSS keeps the framework it is on today.
    |
    | css_framework: tailwind, bootstrap5, bootstrap4
    | frontend:      blade, livewire, vue, react, svelte
    |
    */

    'css_framework' => env('NOTIFICATIONS_CSS_FRAMEWORK'),
    'frontend'      => env('NOTIFICATIONS_FRONTEND'),

    'per_page' => env('NOTIFICATIONS_PER_PAGE', 20),

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
    | API Routes
    |--------------------------------------------------------------------------
    | The JSON endpoints are registered separately from the web routes so an
    | application can disable them, move them, or guard them with something
    | other than Sanctum.
    |
    */

    'api' => [
        'enabled'    => true,
        'prefix'     => 'api/notifications',
        'middleware' => ['api', 'auth:sanctum'],
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
