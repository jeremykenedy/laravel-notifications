<?php

use Jeremykenedy\LaravelNotifications\Services\NotificationService;

it('resolves notification service from container', function () {
    expect(app(NotificationService::class))->toBeInstanceOf(NotificationService::class);
});

it('is a singleton', function () {
    $a = app(NotificationService::class);
    $b = app(NotificationService::class);
    expect($a)->toBe($b);
});

it('config loads defaults', function () {
    expect(config('notifications.enabled'))->toBeTrue();
    expect(config('notifications.per_page'))->toBe(20);
    expect(config('notifications.bell.show_count'))->toBeTrue();
    expect(config('notifications.bell.max_count_display'))->toBe(99);
});

it('has all required methods', function () {
    $service = new NotificationService();
    expect(method_exists($service, 'unreadCount'))->toBeTrue();
    expect(method_exists($service, 'getAll'))->toBeTrue();
    expect(method_exists($service, 'getUnread'))->toBeTrue();
    expect(method_exists($service, 'markAsRead'))->toBeTrue();
    expect(method_exists($service, 'markAllAsRead'))->toBeTrue();
    expect(method_exists($service, 'delete'))->toBeTrue();
    expect(method_exists($service, 'deleteAll'))->toBeTrue();
});
