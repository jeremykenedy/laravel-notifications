<?php

use Jeremykenedy\LaravelNotifications\Services\NotificationService;

it('can instantiate the NotificationService', function () {
    $service = new NotificationService();
    expect($service)->toBeInstanceOf(NotificationService::class);
});

it('has an unreadCount method', function () {
    expect(method_exists(NotificationService::class, 'unreadCount'))->toBeTrue();
});

it('has a getAll method', function () {
    expect(method_exists(NotificationService::class, 'getAll'))->toBeTrue();
});

it('has a getUnread method', function () {
    expect(method_exists(NotificationService::class, 'getUnread'))->toBeTrue();
});

it('has a markAsRead method that accepts user and notification id', function () {
    $reflection = new ReflectionMethod(NotificationService::class, 'markAsRead');
    $params = $reflection->getParameters();
    expect($params)->toHaveCount(2)
        ->and($params[0]->getName())->toBe('user')
        ->and($params[1]->getName())->toBe('notificationId');
});

it('has a markAllAsRead method', function () {
    expect(method_exists(NotificationService::class, 'markAllAsRead'))->toBeTrue();
});

it('has a delete method', function () {
    expect(method_exists(NotificationService::class, 'delete'))->toBeTrue();
});

it('has a deleteAll method', function () {
    expect(method_exists(NotificationService::class, 'deleteAll'))->toBeTrue();
});

it('has the expected return type for markAsRead', function () {
    $reflection = new ReflectionMethod(NotificationService::class, 'markAsRead');
    $returnType = $reflection->getReturnType();
    expect($returnType->getName())->toBe('bool');
});

it('has the expected return type for unreadCount', function () {
    $reflection = new ReflectionMethod(NotificationService::class, 'unreadCount');
    $returnType = $reflection->getReturnType();
    expect($returnType->getName())->toBe('int');
});
