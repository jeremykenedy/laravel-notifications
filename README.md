# Laravel Notifications

A complete in-app notification center for Laravel with bell badge, unread count, mark-as-read, delete, real-time WebSocket broadcasting via Reverb, and a full REST API. Supports Tailwind CSS, Bootstrap 5, Bootstrap 4, with Blade, Livewire, Vue, React, and Svelte frontends.

[![Total Downloads](https://poser.pugx.org/jeremykenedy/laravel-notifications/d/total.svg)](https://packagist.org/packages/jeremykenedy/laravel-notifications)
[![Latest Stable Version](https://poser.pugx.org/jeremykenedy/laravel-notifications/v/stable.svg)](https://packagist.org/packages/jeremykenedy/laravel-notifications)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

#### Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Notification Center Page](#notification-center-page)
  - [Bell Badge with Unread Count](#bell-badge-with-unread-count)
  - [Sending Notifications](#sending-notifications)
  - [Mark as Read](#mark-as-read)
  - [Delete Notifications](#delete-notifications)
- [CSS Framework Support](#css-framework-support)
  - [Tailwind CSS](#tailwind-css)
  - [Bootstrap 5](#bootstrap-5)
  - [Bootstrap 4](#bootstrap-4)
- [Frontend Framework Support](#frontend-framework-support)
  - [Blade with Alpine.js](#blade-with-alpinejs)
  - [Livewire](#livewire)
  - [Vue 3](#vue-3)
  - [React](#react)
  - [Svelte](#svelte)
- [Real-Time Broadcasting](#real-time-broadcasting)
- [Web Routes](#web-routes)
- [API Routes](#api-routes)
- [NotificationService API](#notificationservice-api)
- [Artisan Commands](#artisan-commands)
- [Testing](#testing)
- [License](#license)

## Features

| Feature |
| :--- |
| Full notification center page with read/unread styling |
| Notification titles, messages, and action URLs |
| Type-based icons (welcome, new user, security, etc.) |
| Bell icon with real-time unread count badge |
| Mark individual or all notifications as read |
| Delete individual or all notifications |
| Real-time push via Laravel Reverb WebSockets |
| Fallback polling (configurable interval) |
| `NotificationService` for programmatic access |
| `BroadcastNotificationCreated` auto-listener |
| Paginated notification list |
| Multi-CSS framework: Tailwind, Bootstrap 5, Bootstrap 4 |
| Multi-frontend: Blade/Alpine.js, Livewire 3, Vue 3, React, Svelte |
| REST API with Sanctum authentication |
| Artisan install and switch commands |
| Publishable config, views, and translations |

## Requirements

- PHP 8.2+
- Laravel 12 or 13
- A `notifications` database table (Laravel's built-in migration)

## Installation

```bash
composer require jeremykenedy/laravel-notifications
php artisan notifications:install --css=tailwind --frontend=blade
php artisan migrate
```

The install command publishes the config and views for your chosen CSS and frontend framework.

## Configuration

```bash
php artisan vendor:publish --tag=notifications-config
```

Key settings in `config/notifications.php`:

```php
return [
    'enabled' => true,
    'per_page' => 20,
    'auto_mark_read_on_view' => false,
    'bell' => [
        'show_count' => true,
        'max_count_display' => 99,
        'poll_interval_ms' => 30000,
    ],
    'routes' => [
        'enabled' => true,
        'prefix' => 'notifications',
        'middleware' => ['web', 'auth'],
    ],
    'broadcast' => [
        'enabled' => true,
    ],
];
```

## Usage

### Notification Center Page

Navigate to `/notifications` to see all notifications. The page shows:
- Unread notifications with a blue left border
- Notification title and message
- Relative timestamps ("2 minutes ago")
- Action links when the notification includes a URL
- "Mark All Read" and "Delete All" buttons
- Pagination

### Bell Badge with Unread Count

The bell icon in the nav bar shows the unread notification count. It:
- Fetches the count on page load via `GET /notifications/count`
- Polls every 30 seconds as fallback
- Updates instantly via WebSocket when Reverb is running
- Shows "99+" when count exceeds 99
- Hides completely when count is 0

### Sending Notifications

Use Laravel's built-in notification system. Any notification sent via the `database` channel automatically appears in the notification center:

```php
use App\Notifications\OrderShipped;

$user->notify(new OrderShipped($order));
```

The package automatically broadcasts a `NotificationCreated` event via WebSocket when any database notification is sent, so the bell badge updates in real-time.

### Mark as Read

```php
// Single notification
$user->notifications()->where('id', $id)->first()->markAsRead();

// All notifications
$user->unreadNotifications->markAsRead();

// Via service
app(NotificationService::class)->markAsRead($user, $notificationId);
app(NotificationService::class)->markAllAsRead($user);
```

### Delete Notifications

```php
app(NotificationService::class)->delete($user, $notificationId);
app(NotificationService::class)->deleteAll($user);
```

## CSS Framework Support

The notification center view automatically renders with the correct CSS framework based on `config('ui-kit.css_framework')`.

### Tailwind CSS

Pure Tailwind classes with `dark:` variants. No Bootstrap classes. Uses `<x-ui::*>` components.

### Bootstrap 5

Pure Bootstrap 5 classes (`list-group`, `badge`, `btn`, `d-flex`, `gap-*`). Uses `data-bs-dismiss` for alerts.

### Bootstrap 4

Pure Bootstrap 4 classes (`list-group`, `badge`, `btn`, `d-flex`). Uses `data-dismiss` for alerts and `mr-*` spacing instead of `me-*`.

Switch at any time:

```bash
php artisan notifications:switch --css=bootstrap5
```

## Frontend Framework Support

### Blade with Alpine.js

The default. The notification center page uses `@extends('layouts.app')` with Alpine.js for interactivity (mark read, delete). No JavaScript frameworks needed.

```html
{{-- Already available at /notifications --}}
```

### Livewire

A `NotificationsList` Livewire component with `wire:click` for all actions:

```html
<livewire:notifications-list />
```

Methods: `markAsRead($id)`, `markAllAsRead()`, `delete($id)`. Supports pagination via `WithPagination`.

### Vue 3

An Inertia.js page component:

```bash
php artisan notifications:install --frontend=vue
```

Publishes `NotificationsIndex.vue` to `resources/js/Pages/Notifications/`.

### React

```bash
php artisan notifications:install --frontend=react
```

Publishes `NotificationsIndex.jsx` to `resources/js/Pages/Notifications/`.

### Svelte

```bash
php artisan notifications:install --frontend=svelte
```

Publishes `NotificationsIndex.svelte` to `resources/js/Pages/Notifications/`.

Switch frontend at any time:

```bash
php artisan notifications:switch --frontend=vue
```

## Real-Time Broadcasting

When Laravel Reverb is configured (`BROADCAST_DRIVER=reverb`), the package automatically:

1. Listens for `NotificationSent` events (Laravel's built-in event)
2. Broadcasts a `NotificationCreated` event on `private-notifications.{userId}`
3. The nav bell badge subscribes via Echo and updates the count instantly

Channel authorization in `routes/channels.php`:

```php
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

Frontend subscription (automatic in the nav bar):

```javascript
window.Echo.private('notifications.' + userId)
    .listen('.notification.created', (e) => {
        notifCount = e.count;
    });
```

Broadcasting can be disabled:

```php
// config/notifications.php
'broadcast' => ['enabled' => false],
```

## Web Routes

| Method | URI | Description |
| :--- | :--- | :--- |
| `GET` | `/notifications` | Notification center page |
| `GET` | `/notifications/count` | Unread count as JSON `{"count": 5}` |
| `POST` | `/notifications/{id}/read` | Mark single notification as read |
| `POST` | `/notifications/read-all` | Mark all notifications as read |
| `DELETE` | `/notifications/{id}` | Delete single notification |
| `DELETE` | `/notifications` | Delete all notifications |

All routes require `web` and `auth` middleware.

## API Routes

| Method | URI | Description |
| :--- | :--- | :--- |
| `GET` | `/api/notifications` | Paginated notifications (JSON) |
| `GET` | `/api/notifications/unread` | Unread notifications (JSON) |
| `GET` | `/api/notifications/count` | Unread count (JSON) |
| `POST` | `/api/notifications/{id}/read` | Mark as read |
| `POST` | `/api/notifications/read-all` | Mark all as read |
| `DELETE` | `/api/notifications/{id}` | Delete notification |

API routes require `api` and `auth:sanctum` middleware.

## NotificationService API

```php
use Jeremykenedy\LaravelNotifications\Services\NotificationService;

$service = app(NotificationService::class);

$service->unreadCount($user);              // int
$service->getAll($user, $perPage);         // LengthAwarePaginator
$service->getUnread($user, $perPage);      // LengthAwarePaginator
$service->markAsRead($user, $id);          // void
$service->markAllAsRead($user);            // int (count marked)
$service->delete($user, $id);              // void
$service->deleteAll($user);                // void
```

## Artisan Commands

```bash
# Install with framework selection
php artisan notifications:install --css=tailwind --frontend=blade

# Switch CSS framework
php artisan notifications:switch --css=bootstrap5

# Switch frontend framework
php artisan notifications:switch --frontend=vue

# Switch both at once
php artisan notifications:switch --css=bootstrap4 --frontend=livewire

# Publish config only
php artisan vendor:publish --tag=notifications-config

# Publish views
php artisan vendor:publish --tag=notifications-views
```

## Testing

The package includes comprehensive tests covering:

- **Service layer**: `unreadCount`, `getAll`, `getUnread`, `markAsRead`, `markAllAsRead`, `delete`, `deleteAll`
- **Web routes**: index, count, mark read, mark all read, delete, delete all
- **Authentication**: all routes require auth
- **CSS frameworks**: renders with Tailwind, Bootstrap 5, Bootstrap 4
- **Frontend frameworks**: renders with Blade, Livewire, Vue, React, Svelte
- **All 15 combinations**: every CSS x frontend combination tested
- **Livewire component**: render, markAsRead, markAllAsRead, delete, cross-CSS
- **Broadcasting**: NotificationCreated event channel, broadcast name, properties
- **Structural**: all view directories and JS component files verified

Run tests:

```bash
php artisan test --filter=NotificationsPackageTest
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
