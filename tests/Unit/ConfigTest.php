<?php

use Jeremykenedy\LaravelNotifications\Services\NotificationService;
use Jeremykenedy\LaravelNotifications\Support\Colors;

it('loads the package defaults', function () {
    expect(config('notifications.per_page'))->toBe(20)
        ->and(config('notifications.bell.show_count'))->toBeTrue()
        ->and(config('notifications.bell.max_count_display'))->toBe(99)
        ->and(config('notifications.bell.poll_interval_ms'))->toBe(30000)
        ->and(config('notifications.routes.enabled'))->toBeTrue()
        ->and(config('notifications.routes.prefix'))->toBe('notifications')
        ->and(config('notifications.api.enabled'))->toBeTrue()
        ->and(config('notifications.broadcast.enabled'))->toBeFalse();
});

it('documents only configuration the package actually reads', function () {
    $config = require __DIR__.'/../../config/notifications.php';
    $source = '';

    foreach (['src', 'resources/views', 'routes'] as $dir) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../'.$dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php'], true)) {
                $source .= file_get_contents($file->getPathname());
            }
        }
    }

    $unused = [];

    foreach (array_keys($config) as $key) {
        if (!str_contains($source, "notifications.{$key}")) {
            $unused[] = $key;
        }
    }

    expect($unused)->toBeEmpty();
});

it('resolves the service as a singleton from the container', function () {
    $service = app(NotificationService::class);

    expect($service)->toBe(app(NotificationService::class));
});

it('registers the package translations', function () {
    expect(__('notifications::notifications.notifications'))->toBe('Notifications')
        ->and(__('notifications::notifications.archive_all'))->toBe('Archive all');
});

it('has a translation for every key the views ask for', function () {
    $translations = require __DIR__.'/../../resources/lang/en/notifications.php';

    // The send view and the colour settings widget build their labels by
    // concatenation, so the scan below picks up the bare prefixes rather than
    // the real keys. Both are asserted explicitly in their own tests.
    $dynamicPrefixes = ['type_', 'color_'];
    $used = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../resources/views'));
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        preg_match_all("/notifications::notifications\.([a-z_]+)/", file_get_contents($file->getPathname()), $matches);
        $used = array_merge($used, $matches[1]);
    }

    $missing = array_diff(array_unique($used), array_keys($translations), $dynamicPrefixes);

    expect(array_values($missing))->toBeEmpty();
});

it('has a label and a hint for every configurable colour', function () {
    foreach (Colors::keys() as $key) {
        expect(__("notifications::notifications.color_{$key}"))->not->toBe("notifications::notifications.color_{$key}")
            ->and(__("notifications::notifications.color_{$key}_hint"))->not->toBe("notifications::notifications.color_{$key}_hint");
    }
});

it('has a label for every notification type the send form offers', function () {
    foreach (['info', 'success', 'warning', 'danger', 'system'] as $type) {
        expect(__("notifications::notifications.type_{$type}"))->not->toBe("notifications::notifications.type_{$type}");
    }
});
