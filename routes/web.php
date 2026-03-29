<?php

use Illuminate\Support\Facades\Route;
use Jeremykenedy\LaravelNotifications\Http\Controllers\NotificationController;

Route::group([
    'prefix'     => config('notifications.routes.prefix', 'notifications'),
    'middleware' => config('notifications.routes.middleware', ['web', 'auth']),
], function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/{id}/archive', [NotificationController::class, 'archive'])->name('notifications.archive');
    Route::post('/{id}/unarchive', [NotificationController::class, 'unarchive'])->name('notifications.unarchive');
    Route::post('/archive-all', [NotificationController::class, 'archiveAll'])->name('notifications.archive-all');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
});

// Send notification GUI (separate middleware for admin access)
if (config('notifications.send.enabled', true)) {
    Route::group([
        'prefix'     => config('notifications.routes.prefix', 'notifications').'/send',
        'middleware' => config('notifications.send.middleware', ['web', 'auth', 'level:5']),
    ], function () {
        Route::get('/', [\Jeremykenedy\LaravelNotifications\Http\Controllers\SendNotificationController::class, 'create'])->name('notifications.send.create');
        Route::post('/', [\Jeremykenedy\LaravelNotifications\Http\Controllers\SendNotificationController::class, 'send'])->name('notifications.send.store');
    });
}
