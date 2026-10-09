<?php

use Illuminate\Support\Facades\Route;
use Jeremykenedy\LaravelNotifications\Http\Controllers\Api\NotificationApiController;

Route::group([
    'prefix'     => config('notifications.api.prefix', 'api/notifications'),
    'middleware' => config('notifications.api.middleware', ['api', 'auth:sanctum']),
], function () {
    Route::get('/', [NotificationApiController::class, 'index'])->name('api.notifications.index');
    Route::get('/unread', [NotificationApiController::class, 'unread'])->name('api.notifications.unread');
    Route::get('/count', [NotificationApiController::class, 'count'])->name('api.notifications.count');
    Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/{id}/unread', [NotificationApiController::class, 'markAsUnread'])
        ->name('api.notifications.mark-unread');
    Route::post('/read-all', [NotificationApiController::class, 'markAllAsRead'])->name('api.notifications.read-all');
    Route::post('/{id}/archive', [NotificationApiController::class, 'archive'])->name('api.notifications.archive');
    Route::post('/{id}/unarchive', [NotificationApiController::class, 'unarchive'])
        ->name('api.notifications.unarchive');
    Route::post('/archive-all', [NotificationApiController::class, 'archiveAll'])
        ->name('api.notifications.archive-all');
    Route::delete('/{id}', [NotificationApiController::class, 'destroy'])->name('api.notifications.destroy');
    Route::delete('/', [NotificationApiController::class, 'destroyAll'])->name('api.notifications.destroy-all');
});
