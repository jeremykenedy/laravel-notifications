<?php

use Illuminate\Support\Facades\Route;
use Jeremykenedy\LaravelNotifications\Http\Controllers\NotificationSettingsController;

Route::group([
    'prefix'     => config('notifications.settings.prefix', 'notifications/settings'),
    'middleware' => config('notifications.settings.middleware', ['web', 'auth']),
], function () {
    Route::get('/', [NotificationSettingsController::class, 'edit'])->name('notifications.settings.edit');
    Route::put('/', [NotificationSettingsController::class, 'update'])->name('notifications.settings.update');
    Route::delete('/', [NotificationSettingsController::class, 'reset'])->name('notifications.settings.reset');
});
