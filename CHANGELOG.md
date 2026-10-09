# Changelog

All notable changes to this package are documented here.

## 3.0.0 - 2026-10-09

The major release of everything in 2.1.0, plus internal refactors and static analysis configuration that do not change behavior.

2.1.0 added a database table, new routes and new config, and turned on by default a settings page that any signed in user can use to change the notification colours for everyone. That is a change to the security posture and the schema of an existing installation, which is a contract change and not a compatible feature, so it is released as 3.0.0. The 2.1.0 tag stays where it is.

### Fixed

- `php artisan migrate` failed on a fresh application that followed the README. Since 2.0.0 the `archived_at` migration created the `notifications` table when it found none, and because it is dated before any migration the application generates, it ran first, so Laravel's own `create_notifications_table` then failed with "table already exists". The package never creates the table now. The column is added by a migration named `2099_01_01_000000_add_archived_at_to_notifications_table`, which sorts after the application's own, and the old file is kept as a no-op for installs that already ran it. Found by running the install and upgrade flows in a real Laravel application, which the package's own test suite could not reproduce because it never runs the two migrations together.
- The same migration silently recorded itself as run when it found no table, and an application that created the table afterwards never got the column. It still does nothing when there is no table, and the README now says how to re-run it.

### Changed

- `NotificationService` no longer repeats the find, check, update shape in four methods. It lives in one protected `withNotification()` helper, so the ownership check cannot drift between them. Public signatures and behavior are unchanged, and a missing or foreign notification is still never touched.
- `notifications:switch` is split into small methods with identical output and exit codes. The settings validation that `save()` and `apply()` both did is now one method.
- Colour channels are named instead of single letters, `NotificationService::send()` returns early instead of using `else`, and the settings controller no longer assigns inside a condition.
- The CI workflow references all third party actions by commit SHA instead of by tag, so a moved tag cannot silently change what runs.

### Added

- `phpcs.xml`, `phpmd.xml`, `.markdownlint.json` and `.codacy.yml`, so static analysis checks the package against the standard it is actually written to. Codacy grades the package A with a score of 100 and no issues.
- A Codacy grade badge in the README.

### Upgrading

- Run `php artisan migrate` to create the `notification_settings` table.
- Set `settings.middleware` to something that authorizes your administrators, or set `settings.route_enabled` to `false`. The default of `['web', 'auth']` lets any signed in user change the colours.
- See the Upgrading section of the README for the steps from 2.0 and from 1.x.

## 2.1.0 - 2026-10-09

### Added

- Notification colours are configurable. One hex value per type, plus unread, read and badge accents, drives the icon, its background, the row tint and the border together.
- A colour settings page at `/notifications/settings` with a native picker and hex field per colour, a reset control on any value that differs from the default, and a live preview of every notification type that updates while you type. The page extends the layout named in `settings.blade_extended` and is guarded by `settings.middleware`.
- `notifications::partials.color-settings`, a self contained Blade partial that puts the same picker on any page of your own. It brings its own markup, styles and behaviour so it renders the same on all three CSS frameworks.
- `notifications::partials.colors`, which publishes the colours as CSS custom properties so your own views can match. Five properties per colour: the colour, a 14 percent tint, a 7 percent row wash, a 35 percent border, and a readable black or white foreground computed from WCAG relative luminance.
- `Support\Colors` and `Support\Settings` for reading and storing colours from PHP.
- A `notification_settings` table, created by a new migration. Colours saved there are overlaid onto the config at boot, so everything downstream keeps reading `config('notifications.colors.*')`.
- `docs/colors.md`, linked from a new Documentation section in the README.

### Changed

- The bell badge on all three CSS frameworks and the Livewire list now read the configured colours. The bell partial includes the colour variables itself, so it works in a navbar without the notification center page, and the variables are emitted once per page however many partials include them.
- Bootstrap 5 and Bootstrap 4 now colour notifications by type. Previously only Tailwind did, and the Bootstrap views used one fixed accent for every type.
- The settings routes are registered before the web routes, because `notifications/settings` would otherwise be captured by the `notifications/{id}` routes and a reset would delete a notification instead.
- Tints are computed in PHP rather than with `color-mix()`, so output does not depend on a CSS feature being available.
- The colour picker follows the host page for dark mode, reading Tailwind's `.dark` class and Bootstrap 5's `data-bs-theme`, rather than the operating system setting.

## 2.0.0 - 2026-09-11

### Fixed

- `notifications:switch` imported a trait from `jeremykenedy/laravel-ui-kit`, which is not a dependency. Resolving the command threw `Trait not found`, and because the service provider registers commands on boot, that took down every artisan command in the host application. The trait now lives in this package as `Jeremykenedy\LaravelNotifications\Console\Concerns\HandlesFrameworkSetup`.
- The Tailwind notification center and send views were built from `<x-ui::*>` components that this package never registered, so the default views returned a 500. Both views are now self contained.
- The package read `config('ui-kit.css_framework')` and `config('ui-kit.frontend')` but shipped no such config, so framework switching did nothing on its own. Added `notifications.css_framework` and `notifications.frontend`, which fall back to the `ui-kit` values when empty.
- Sending a notification to a role threw a `TypeError`, because a form supplies `role_id` as a string and the controller declared an `int` parameter under `strict_types`.
- `NotificationCreated::$userId` was typed `int`, so broadcasting threw for applications keyed by UUID or ULID. The listener caught and discarded the error, leaving broadcasting silently dead. The type is now `int|string`.
- The broadcast listener swallowed every exception in an empty `catch`. Failures are now reported to the exception handler, and still never fail the notification.
- The `archived_at` migration failed when the `notifications` table did not exist yet. Both `up` and `down` now check for the table first.
- The bell partial only ever existed in Tailwind markup. Bootstrap 5 and Bootstrap 4 now have their own.
- Notification titles containing a quote or newline broke the Alpine filter expression, which was escaped with `addslashes`.
- The send form interpolated old input straight into an Alpine attribute. Entity escaping does not help there, because the browser decodes the attribute before Alpine evaluates it, so a rejected `audience` value could break out of the expression. It is serialized with `@js` now.
- The `archived_at` migration skipped silently when the `notifications` table was absent, which is the normal state on a fresh install, because Laravel generates its own notifications migration on demand and it usually sorts later. The migration was then recorded as complete and the column never appeared. It creates the table when there is none.
- `archiveAll` left unread notifications unread, while archiving one at a time marked it read.
- The send form's email checkbox was ignored for role audiences, because `sendToRole` had no way to pass the flag through.

### Added

- A test suite covering the service layer, every web and JSON route, view rendering across all three CSS frameworks, the send GUI, all thirty install and switch combinations, the migration, and broadcasting for both integer and UUID keys.
- GitHub Actions running tests on PHP 8.2, 8.3 and 8.4 against Laravel 12 and 13, plus Pint and a frontend asset check.
- `pint.json`, with the rules that conflict with StyleCI disabled so the two do not fight. StyleCI keeps running on its own defaults, which is what it did before.
- `notifications.api` config block: the JSON endpoints can be disabled, moved, or guarded by something other than Sanctum.
- `bell.show_count` and `bell.max_count_display` are now read by the bell partial.
- A `notifications-lang` publish tag.
- Light and dark README banners.

### Changed

- `notifications:install` and `notifications:switch` write `NOTIFICATIONS_CSS_FRAMEWORK` and `NOTIFICATIONS_FRONTEND` instead of `UI_KIT_CSS` and `UI_KIT_FRONTEND`. The `UI_KIT_*` values are still honoured as a fallback.
- The Livewire component delegates to `NotificationService` and lists the inbox rather than every notification, matching the Blade view.
- Sending to a role that cannot be found returns a validation error rather than silently sending to the `user` role.
- Send validation moved to a `SendNotificationRequest` form request.
- Every user facing string in every view now comes from the translation file, across all three CSS frameworks and the Livewire component.
- `sendToRole` takes an optional trailing `$sendEmail` argument. Existing calls are unaffected.
- Accessibility pass over the views: labelled icon buttons, visible focus rings, a labelled filter input, `aria-labelledby` on both modals, Escape closes them, and the pulse animation respects `prefers-reduced-motion`.

### Removed

- The `enabled`, `auto_mark_read_on_view` and `confirm_style` config keys. No code in the package read any of them.

## 1.1.0 - 2026-09-11

### Added

- Five JSON endpoints that the web routes already had: mark as unread, archive, restore from archive, archive all, and delete all. Thanks to @badeeb in #2.

## 1.0.0 - 2026-03-29

Initial release.
