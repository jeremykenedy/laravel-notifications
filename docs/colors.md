# Notification Colors

Every colour a notification is drawn with comes from one place, so changing a
single value recolours the icon, its background, the row tint and the border
together. Colours can be set in the config file, or from a settings page in the
browser when you want people to change them without a deploy.

## The colours

| Key | Used for |
| :--- | :--- |
| `info` | The default type, used when a notification does not set one |
| `success` | Confirmations and completed work |
| `warning` | Something that needs attention soon |
| `danger` | Failures and destructive outcomes |
| `system` | Announcements from the application itself |
| `unread` | The dot and highlight on notifications not yet read |
| `read` | Notifications that have already been read |
| `badge` | The unread count badge on the bell |

Each value is a six digit hex colour. Anything else is rejected by validation,
and a malformed value already in storage falls back to the shipped default
rather than blanking the interface.

## Setting colours in config

```php
// config/notifications.php
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
```

This is all you need if colours are fixed for your application. The settings
page and its table are only required when people should be able to change them
at runtime.

## The settings page

Run the migration so colours have somewhere to live:

```bash
php artisan migrate
```

The page is then at `/notifications/settings`. It shows a native colour picker
and a hex field for each colour, a reset control on any value that differs from
the default, and a live preview of each notification type that updates as you
type, before anything is saved.

Saved colours are written to one row in the `notification_settings` table and
overlaid onto the config when the application boots, so everything downstream
keeps reading `config('notifications.colors.*')` and never has to know whether a
value came from the file or the database.

### Putting the page inside your own layout

```php
// config/notifications.php
'settings' => [
    'blade_extended' => 'layouts.app',
],
```

The page extends whatever you name here, so it sits inside your application's
own navigation and chrome. Publish the views if you want to change the page
itself:

```bash
php artisan vendor:publish --tag=notifications-views
```

### Locking the page down

```php
'settings' => [
    'middleware' => ['web', 'auth', 'can:manage-notifications'],
],
```

Any middleware stack works. The shipped default is `['web', 'auth']`, which
means any signed in user can change the colours, so add your own authorization
if that is not what you want.

### Turning it off

```php
'settings' => [
    'enabled'       => false,  // stop reading saved colours, config file wins
    'route_enabled' => false,  // stop registering the page at all
],
```

`enabled` controls whether stored colours are applied. `route_enabled` controls
whether the page exists. Turning the routes off leaves the rest of the package
untouched.

## Where the colours apply

| Surface | Reads |
| :--- | :--- |
| Notification center, all three CSS frameworks | The type colour, or the unread and read accents |
| Bell badge, all three CSS frameworks | `badge`, with a readable foreground |
| Livewire list | The type colour, or the read accent |
| Vue, React and Svelte starter components | Nothing. They are starting points you style yourself |

The bell partial includes the colour variables itself, so it picks them up in a
navbar on pages that never render the notification center.

## Putting the picker on your own page

The picker is a self contained Blade partial. Include it anywhere:

```blade
@include('notifications::partials.color-settings')
```

It brings its own markup, styles and behaviour, so it looks the same on
Tailwind, Bootstrap 5 and Bootstrap 4, and it posts to the package's own
settings routes. The routes still have to be registered, so leave
`settings.route_enabled` on even if you never link to the page itself.

## Using the colours in your own views

Colours are published as CSS custom properties. Pull them in once per page:

```blade
@include('notifications::partials.colors')
```

Then reference them:

```html
<span style="background-color: var(--notifications-success-tint); color: var(--notifications-success);">
    Done
</span>
```

Five properties are published per colour:

| Property | What it is |
| :--- | :--- |
| `--notifications-{key}` | The colour itself |
| `--notifications-{key}-tint` | The colour at 14 percent, for icon backgrounds |
| `--notifications-{key}-row` | The colour at 7 percent, for row backgrounds |
| `--notifications-{key}-border` | The colour at 35 percent, for borders |
| `--notifications-{key}-on` | Black or white, whichever stays readable on the colour |

The tints are computed in PHP rather than with `color-mix()`, so the output does
not depend on a CSS feature being available in the browser. `-on` uses the WCAG
relative luminance formula, so a pale pick gets dark text instead of white.

## Reading colours in PHP

```php
use Jeremykenedy\LaravelNotifications\Support\Colors;

Colors::get('success');          // '#16a34a'
Colors::all();                   // every colour, keyed
Colors::rgba('#16a34a', 0.14);   // 'rgba(22, 163, 74, 0.14)'
Colors::readableOn('#16a34a');   // '#ffffff'
Colors::cssVariables();          // the declaration block the partial emits
```

```php
use Jeremykenedy\LaravelNotifications\Support\Settings;

$settings = app(Settings::class);

$settings->save(['success' => '#0a7d32']);  // store and apply
$settings->stored();                         // what is in the table
$settings->defaults();                       // the shipped values
$settings->reset();                          // drop stored, restore defaults
$settings->available();                      // false before the migration runs
```

## Dark mode

The picker follows the host page rather than the operating system, so it does
not go dark inside a light layout. It reads Tailwind's `.dark` class and
Bootstrap 5's `data-bs-theme="dark"`.
