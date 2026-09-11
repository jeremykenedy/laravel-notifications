# Changelog

All notable changes to this package are documented here.

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
