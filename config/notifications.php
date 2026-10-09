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

    /*
    |--------------------------------------------------------------------------
    | Colors
    |--------------------------------------------------------------------------
    | One hex colour per notification type, plus the accents used for unread
    | and read rows and the bell badge. Every shade a view needs is derived
    | from these, so a single value drives the icon, its background, the row
    | tint and the border.
    |
    | These are the shipped defaults. When the settings page is enabled and the
    | settings table exists, values saved there are overlaid on top of this at
    | boot and the form's reset control restores whatever is written here.
    |
    */

    'colors' => [
        'info'    => '#2563eb',
        'success' => '#16a34a',
        'warning' => '#d97706',
        'danger'  => '#dc2626',
        'system'  => '#7c3aed',
        'unread'  => '#2563eb',
        'read'    => '#6b7280',
        'badge'   => '#ef4444',
    ],

    /*
    |--------------------------------------------------------------------------
    | Colour Settings Page
    |--------------------------------------------------------------------------
    | The settings page lets people change the colours above from the browser
    | and stores them in the settings table. Turn `enabled` off to keep the
    | config file as the only source of colours.
    |
    | `blade_extended` is the layout the published page extends. Point it at
    | your own layout so the page sits inside your application's chrome.
    |
    */

    'settings' => [
        'enabled'        => env('NOTIFICATIONS_SETTINGS_ENABLED', true),
        'route_enabled'  => env('NOTIFICATIONS_SETTINGS_ROUTE_ENABLED', true),
        'prefix'         => 'notifications/settings',
        'middleware'     => ['web', 'auth'],
        'blade_extended' => 'layouts.app',
        'table'          => 'notification_settings',
        'connection'     => null,
    ],

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
