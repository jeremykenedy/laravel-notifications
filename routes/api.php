<?php

use Illuminate\Support\Facades\Route;
use Jeremykenedy\LaravelNotifications\Http\Controllers\Api\NotificationApiController;

Route::group([
    'prefix'     => 'api/notifications',
    'middleware' => ['api', 'auth:sanctum'],
], function () {
    Route::get('/', [NotificationApiController::class, 'index'])->name('api.notifications.index');
    Route::get('/unread', [NotificationApiController::class, 'unread'])->name('api.notifications.unread');
    Route::get('/count', [NotificationApiController::class, 'count'])->name('api.notifications.count');
    Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/read-all', [NotificationApiController::class, 'markAllAsRead'])->name('api.notifications.read-all');
    Route::delete('/{id}', [NotificationApiController::class, 'destroy'])->name('api.notifications.destroy');
});
